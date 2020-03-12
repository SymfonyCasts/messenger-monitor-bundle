<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\Storage;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\PhpUnit\ClockMock;
use SymfonyCasts\MessengerMonitorBundle\Statistics\MetricsPerMessageType;
use SymfonyCasts\MessengerMonitorBundle\Statistics\Statistics;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\AbstractDoctrineIntegrationTests;

final class DoctrineConnectionTest extends AbstractDoctrineIntegrationTests
{
    public function testSaveAndLoadMessage(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        $this->doctrineConnection->saveMessage(
            new StoredMessage($uuid, TestableMessage::class, $dispatchedAt = (new \DateTimeImmutable())->setTime(0, 0, 0, 1000))
        );

        $storedMessage = $this->doctrineConnection->findMessage($uuid);

        $this->assertEquals(new StoredMessage($uuid, TestableMessage::class, $dispatchedAt, 1), $storedMessage);
    }

    public function testSaveSeveralMessages(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);
        $uuid2 = uuid_create(UUID_TYPE_RANDOM);

        $this->doctrineConnection->saveMessage(new StoredMessage($uuid, TestableMessage::class, new \DateTimeImmutable()));
        $this->doctrineConnection->saveMessage(new StoredMessage($uuid2, TestableMessage::class, new \DateTimeImmutable()));

        $this->assertInstanceOf(StoredMessage::class, $this->doctrineConnection->findMessage($uuid));
        $this->assertInstanceOf(StoredMessage::class, $this->doctrineConnection->findMessage($uuid2));
    }

    public function testUpdateMessage(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        ClockMock::register(StoredMessage::class);
        ClockMock::withClockMock((new \DateTimeImmutable('2020-01-01 00:00:01.123'))->format('U.u'));

        $this->doctrineConnection->saveMessage($storedMessage = new StoredMessage($uuid, TestableMessage::class, new \DateTimeImmutable('2020-01-01 00:00:00.123')));
        $storedMessage->updateWaitingTime();
        $storedMessage->setReceiverName('receiver_name');
        $storedMessage->updateHandlingTime();
        $storedMessage->updateFailingTime();
        $this->doctrineConnection->updateMessage($storedMessage);

        $storedMessageLoadedFromDatabase = $this->doctrineConnection->findMessage($uuid);

        $this->assertSame($storedMessage->getWaitingTime(), $storedMessageLoadedFromDatabase->getWaitingTime());
        $this->assertSame($storedMessage->getReceiverName(), $storedMessageLoadedFromDatabase->getReceiverName());
        $this->assertSame($storedMessage->getHandlingTime(), $storedMessageLoadedFromDatabase->getHandlingTime());
        $this->assertSame($storedMessage->getFailingTime(), $storedMessageLoadedFromDatabase->getFailingTime());
    }

    public function testGetStatistics(): void
    {
        $statistics = $this->doctrineConnection->getStatistics($fromDate = new \DateTimeImmutable(), $toDate = new \DateTimeImmutable());
        $this->assertEquals(new Statistics($fromDate, $toDate), $statistics);

        $this->storeMessages();

        $statistics = $this->doctrineConnection->getStatistics($fromDate = new \DateTimeImmutable('1 hour ago'), $toDate = new \DateTimeImmutable());

        $expectedStatistics = new Statistics($fromDate, $toDate);
        $expectedStatistics->add(new MetricsPerMessageType($fromDate, $toDate, TestableMessage::class, 2, 0.2, 0.3));
        $expectedStatistics->add(new MetricsPerMessageType($fromDate, $toDate, 'Another'.TestableMessage::class, 2, 0.1, 0.2));

        $this->assertEquals($expectedStatistics, $statistics);
    }

    private function storeMessages(): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        $uuid = uuid_create(UUID_TYPE_RANDOM);
        $uuid2 = uuid_create(UUID_TYPE_RANDOM);
        $uuid3 = uuid_create(UUID_TYPE_RANDOM);

        $connection->insert(
            'messenger_monitor',
            [
                'message_uid' => $uuid,
                'class' => TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('3 minutes ago'))->format('U.u'),
                'waiting_time' => 0.1,
                'handling_time' => 0.2,
            ]
        );

        $connection->insert(
            'messenger_monitor',
            [
                'message_uid' => $uuid2,
                'class' => TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('10 minutes ago'))->format('U.u'),
                'waiting_time' => 0.3,
                'handling_time' => 0.4,
            ]
        );

        $connection->insert(
            'messenger_monitor',
            [
                'message_uid' => $uuid3,
                'class' => 'Another'.TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('3 minutes ago'))->format('U.u'),
                'waiting_time' => 0.1,
                'handling_time' => 0.2,
            ]
        );

        // this one should only affect waiting_time metric
        // proves that "null" values are not assumed as "0" in AVG() sql function
        $connection->insert(
            'messenger_monitor',
            [
                'message_uid' => $uuid3,
                'class' => 'Another'.TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('3 minutes ago'))->format('U.u'),
                'waiting_time' => 0.1,
            ]
        );

        // should not be part of statistics because it is too old
        $connection->insert(
            'messenger_monitor',
            [
                'message_uid' => $uuid2,
                'class' => TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('6 hours ago'))->format('U.u'),
                'waiting_time' => 1,
                'handling_time' => 2,
            ]
        );
    }
}
