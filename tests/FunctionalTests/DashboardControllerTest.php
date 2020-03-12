<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineReceiver;
use Symfony\Component\Messenger\Transport\Receiver\SingleMessageReceiver;
use Symfony\Component\Messenger\Worker;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestKernel;

final class DashboardControllerTest extends WebTestCase
{
    private $client;
    /** @var MessageBusInterface $messageBus */
    private $messageBus;

    protected static function createKernel(array $options = [])
    {
        return new TestKernel();
    }

    public static function setUpBeforeClass(): void
    {
        self::createClient();

        /** @var Connection $connection */
        $connection = self::$container->get('doctrine.dbal.default_connection');

        try {
            $connection->connect();
        } catch (\Exception $exception) {
            self::markTestSkipped(sprintf('Can\'t connect to connection: %s', $exception->getMessage()));
        }

        $connection->executeQuery('DROP TABLE IF EXISTS messenger_monitor');
        $connection->executeQuery('DROP TABLE IF EXISTS messenger_messages');
    }

    public function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = self::createClient();

        $this->messageBus = self::$container->get('test.messenger.bus.default');
    }

    public function testDashboardEmpty(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertFailedMessagesCount(0, $crawler);
    }

    public function testDashboardWithOneQueuedMessage(): void
    {
        $envelope = $this->dispatchMessage();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 1, 'failed' => 0], $crawler);
        $this->assertFailedMessagesCount(1, $crawler);
        $this->assertStoredMessageIsInDB($envelope);

        $this->handleMessage($envelope, 'queue');
        $this->testDashboardEmpty();
    }

    public function testDashboardWithOneFailedMessage(): void
    {
        $envelope = $this->dispatchMessage(true);
        $this->handleMessage($envelope, 'queue');

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $this->assertQueuesCounts(['queue' => 0, 'failed' => 1], $crawler);
    }

    private function dispatchMessage(bool $willFail = false): Envelope
    {
        return $this->messageBus->dispatch(new Envelope(new TestableMessage($willFail)));
    }

    private function assertQueuesCounts(array $expectedQueues, Crawler $crawler): void
    {
        $this->assertSame(count($expectedQueues), $crawler->filter('#transports-list tr')->count() - 1);

        $queues = [];
        foreach (range(1, count($expectedQueues)) as $item) {
            $queue = $crawler->filter('#transports-list tr')->eq($item);
            $queues[$queue->filter('td')->first()->text()] = (int) $queue->filter('td')->last()->text();
        }
        $this->assertSame($expectedQueues, $queues);
    }

    private function assertFailedMessagesCount(int $count, Crawler $crawler): void
    {
        if ($count === 0) {
            $this->assertSame(1, $crawler->filter('#failed-messages-list tr')->count() - 1);
            $this->assertSame(1, $crawler->filter('#failed-messages-list tr td')->count());

            return;
        }

        $this->assertSame(
            $count,
            $crawler->filter('#failed-messages-list tr')->count() - 1
        );
    }

    private function assertStoredMessageIsInDB(Envelope $envelope): void
    {
        /** @var Connection $connection */
        $connection = self::$container->get('doctrine.dbal.default_connection');

        /** @var MonitorIdStamp $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);
        $this->assertNotFalse(
            $connection->executeQuery('SELECT id FROM messenger_monitor WHERE id = :id', ['id' => $monitorIdStamp->getId()])
        );
    }

    private function handleMessage(Envelope $envelope, string $queueName): void
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::$container->get('event_dispatcher');
        $eventDispatcher->addSubscriber($subscriber = new StopWorkerOnMessageLimitListener(1));

        /** @var ServiceProviderInterface $receiverLocator */
        $receiverLocator = self::$container->get('test.messenger.receiver_locator');

        /** @var DoctrineReceiver $receiver */
        $receiver = $receiverLocator->get($queueName);

        /** @var TransportMessageIdStamp $transportMessageIdStamp */
        $transportMessageIdStamp = $envelope->last(TransportMessageIdStamp::class);

        $worker = new Worker(
            [$queueName => new SingleMessageReceiver($receiver, $receiver->find($transportMessageIdStamp->getId()))],
            $this->messageBus,
            $eventDispatcher
        );
        $worker->run();

        $eventDispatcher->removeSubscriber($subscriber);
    }
}
