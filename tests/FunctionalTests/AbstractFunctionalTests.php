<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\FailureMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\Message;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\RetryableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestKernel;

abstract class AbstractFunctionalTests extends WebTestCase
{
    protected KernelBrowser $client;
    protected MessageBusInterface $messageBus;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return TestKernel::withMessengerConfig(
            [
                'reset_on_message' => true,
                'failure_transport' => 'failed',
                'transports' => [
                    'queue' => [
                        'dsn' => 'doctrine://default?queue_name=queue',
                        'retry_strategy' => ['max_retries' => 0],
                    ],
                    'queue_with_retry' => [
                        'dsn' => 'doctrine://default?queue_name=queue_with_retry',
                        'retry_strategy' => ['max_retries' => 1, 'delay' => 0, 'multiplier' => 1],
                    ],
                    'failed' => 'doctrine://default?queue_name=failed',
                ],
                'routing' => [
                    TestableMessage::class => 'queue',
                    FailureMessage::class => 'queue',
                    RetryableMessage::class => 'queue_with_retry',
                ],
            ]
        );
    }

    protected function setUp(): void
    {
        $this->client = self::createClient([], ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'password']);

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        try {
            $connection->connect();
        } catch (\Exception $exception) {
            self::markTestSkipped(sprintf('Can\'t connect to connection: %s', $exception->getMessage()));
        }

        $connection->executeQuery('DROP TABLE IF EXISTS messenger_messages');
        $connection->executeQuery('DROP TABLE IF EXISTS messenger_monitor');

        self::getContainer()->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection')->configureSchema(new Schema(), $connection);

        $this->messageBus = self::getContainer()->get('test.messenger.bus.default');
    }

    protected function dispatchMessage(Message $message): Envelope
    {
        return $this->messageBus->dispatch(new Envelope($message));
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

    protected function assertStoredMessageIsInDB(Envelope $envelope, int $count = 1): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        /** @var MonitorIdStamp $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        $this->assertSame(
            $count,
            (int) $connection->executeQuery(
                'SELECT count(id) as count FROM messenger_monitor WHERE message_uid = :message_uid',
                ['message_uid' => $monitorIdStamp->getId()]
            )->fetchOne()
        );
    }

    protected function handleLastMessageInQueue(string $queueName): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $eventDispatcher->addSubscriber($subscriber = new StopWorkerOnMessageLimitListener(1));

        $receiver = $this->getReceiver($queueName);

        $worker = new Worker(
            [$queueName => new SingleMessageReceiver($receiver, $receiver->find($this->getLastMessageId($queueName)))],
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

    protected function getLastMessageId(string $queueName): mixed
    {
        $receiver = $this->getReceiver($queueName);

        return $this->getMessageId(current($receiver->get()));
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
