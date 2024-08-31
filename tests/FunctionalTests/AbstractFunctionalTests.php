<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\SingleMessageReceiver;
use Symfony\Component\Messenger\Worker;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestKernel;

abstract class AbstractFunctionalTests extends WebTestCase
{
    protected KernelBrowser $client;
    protected MessageBusInterface $messageBus;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel();
    }

    protected function setUp(): void
    {
        $this->client = self::createClient([], ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'password']);

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        try {
            $connection->connect();
        } catch (\Exception $exception) {
            self::markTestSkipped(\sprintf('Can\'t connect to connection: %s', $exception->getMessage()));
        }

        $connection->executeQuery('DROP TABLE IF EXISTS messenger_messages');

        $databasePlatform = $connection->getDatabasePlatform()->getName();

        $truncateTable = match ($databasePlatform) {
            'mysql' => 'TRUNCATE TABLE messenger_monitor',
            'postgresql' => 'TRUNCATE TABLE messenger_monitor RESTART IDENTITY',
            default => throw new InvalidConfigurationException(\sprintf('Doctrine platform "%s" is not supported', $databasePlatform)),
        };

        try {
            $connection->executeQuery($truncateTable);
        } catch (\Throwable) {
            self::getContainer()->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection')->executeSchema(new Schema(), $connection);
        }

        $this->messageBus = self::getContainer()->get('test.messenger.bus.default');
    }

    protected function dispatchMessage(bool $willFail = false): Envelope
    {
        return $this->messageBus->dispatch(new Envelope(new TestableMessage($willFail)));
    }

    protected function assertQueuesCounts(array $expectedQueues, Crawler $crawler): void
    {
        $this->assertSame(\count($expectedQueues), $crawler->filter('#transports-list tr')->count() - 1);

        $queues = [];
        foreach (range(1, \count($expectedQueues)) as $item) {
            $queue = $crawler->filter('#transports-list tr')->eq($item);
            $queues[$queue->filter('td')->first()->text(null, false)] = (int) $queue->filter('td')->last()->text(null, false);
        }
        $this->assertSame($expectedQueues, $queues);

        foreach ($expectedQueues as $queueName => $messageCount) {
            $receiver = $this->getReceiver($queueName);
            $this->assertCount($messageCount, $receiver->all());
        }
    }

    protected function assertFailedMessagesCount(int $count, Crawler $crawler): void
    {
        if (0 === $count) {
            $this->assertSame(1, $crawler->filter('#failed-messages-list tr')->count() - 1);
            $this->assertSame(1, $crawler->filter('#failed-messages-list tr td')->count());

            return;
        }

        $this->assertSame(
            $count,
            $crawler->filter('#failed-messages-list tr')->count() - 1
        );
    }

    protected function assertStoredMessageIsInDB(Envelope $envelope): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        /** @var MonitorIdStamp $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);
        $this->assertNotFalse(
            $connection->executeQuery('SELECT id FROM messenger_monitor WHERE message_uid = :id', ['id' => $monitorIdStamp->getId()])
        );
    }

    protected function handleMessage(Envelope $envelope, string $queueName): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $eventDispatcher->addSubscriber($subscriber = new StopWorkerOnMessageLimitListener(1));

        $receiver = $this->getReceiver($queueName);

        $worker = new Worker(
            [$queueName => new SingleMessageReceiver($receiver, $receiver->find($this->getMessageId($envelope)))],
            $this->messageBus,
            $eventDispatcher
        );
        $worker->run();

        $eventDispatcher->removeSubscriber($subscriber);
    }

    protected function getMessageId(Envelope $envelope): mixed
    {
        /** @var TransportMessageIdStamp $transportMessageIdStamp */
        $transportMessageIdStamp = $envelope->last(TransportMessageIdStamp::class);

        return $transportMessageIdStamp->getId();
    }

    protected function getLastFailedMessageId(): mixed
    {
        $receiver = $this->getReceiver('failed');

        return $this->getMessageId(current($receiver->get()));
    }

    protected function assertAlertIsPresent(Crawler $crawler, string $class, string $text): void
    {
        $this->assertSame(1, $crawler->filter($class)->count());
        $this->assertStringContainsString($text, $crawler->filter($class)->text(null, false));
    }

    private function getReceiver(string $queueName): ListableReceiverInterface
    {
        /** @var ServiceProviderInterface $receiverLocator */
        $receiverLocator = self::getContainer()->get('test.messenger.receiver_locator');

        return $receiverLocator->get($queueName);
    }
}
