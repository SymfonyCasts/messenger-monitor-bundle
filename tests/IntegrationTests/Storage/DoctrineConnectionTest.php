<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\Storage;

use Doctrine\DBAL\Connection;
use SymfonyCasts\MessengerMonitorBundle\Statistics\MetricsPerMessageType;
use SymfonyCasts\MessengerMonitorBundle\Statistics\Statistics;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\AbstractDoctrineIntegrationTests;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class DoctrineConnectionTest extends AbstractDoctrineIntegrationTests
{
    public function testSaveAndLoadMessage(): void
    {
        $this->doctrineConnection->saveMessage(
            new StoredMessage('id', 'message_uid', TestableMessage::class, $dispatchedAt = (new \DateTimeImmutable())->setTime(0, 0, 0))
        );

        $storedMessage = $this->doctrineConnection->findMessage('message_uid');

        $this->assertEquals(new StoredMessage('id', 'message_uid', TestableMessage::class, $dispatchedAt), $storedMessage);
    }

    public function testSaveSeveralMessages(): void
    {
        $this->doctrineConnection->saveMessage(new StoredMessage('id1', 'message_uid', TestableMessage::class, new \DateTimeImmutable()));
        $this->doctrineConnection->saveMessage(new StoredMessage('id2', 'message_uid', TestableMessage::class, new \DateTimeImmutable()));

        $this->assertInstanceOf(StoredMessage::class, $this->doctrineConnection->findMessage('message_uid'));
        $this->assertInstanceOf(StoredMessage::class, $this->doctrineConnection->findMessage('message_uid'));
    }

    public function testUpdateMessage(): void
    {
        $this->doctrineConnection->saveMessage($storedMessage = new StoredMessage('id', 'message_uid', TestableMessage::class, new \DateTimeImmutable()));
        $storedMessage->setReceivedAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $storedMessage->setHandledAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $storedMessage->setFailedAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $storedMessage->setReceiverName('receiver_name');
        $this->doctrineConnection->updateMessage($storedMessage);

        $storedMessageLoadedFromDatabase = $this->doctrineConnection->findMessage('message_uid');

        $this->assertSame(
            $storedMessage->getReceivedAt()->format('Y-m-d H:i:s'),
            $storedMessageLoadedFromDatabase->getReceivedAt()->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            $storedMessage->getHandledAt()->format('Y-m-d H:i:s'),
            $storedMessageLoadedFromDatabase->getHandledAt()->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            $storedMessage->getFailedAt()->format('Y-m-d H:i:s'),
            $storedMessageLoadedFromDatabase->getFailedAt()->format('Y-m-d H:i:s')
        );

        $this->assertSame($storedMessage->getReceiverName(), $storedMessageLoadedFromDatabase->getReceiverName());
    }

    public function testGetStatistics(): void
    {
        $statistics = $this->doctrineConnection->getStatistics($fromDate = new \DateTimeImmutable(), $toDate = new \DateTimeImmutable());
        $this->assertEquals(new Statistics($fromDate, $toDate), $statistics);

        $this->storeMessages();

        $statistics = $this->doctrineConnection->getStatistics($fromDate = new \DateTimeImmutable('1 hour ago'), $toDate = new \DateTimeImmutable());

        $expectedStatistics = new Statistics($fromDate, $toDate);
        $expectedStatistics->add(new MetricsPerMessageType($fromDate, $toDate, TestableMessage::class, 2, 120.0, 120.0));
        $expectedStatistics->add(new MetricsPerMessageType($fromDate, $toDate, 'Another'.TestableMessage::class, 1, 60.0, 60.0));

        $this->assertEquals($expectedStatistics, $statistics);
    }

    private function storeMessages(): void
    {
        /** @var Connection $connection */
        $connection = self::$container->get('doctrine.dbal.default_connection');

        $connection->insert(
            'messenger_monitor',
            [
                'id' => ':id_1',
                'message_uid' => 'message_uid_1',
                'class' => TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('3 minutes ago'))->format('Y-m-d H:i:s'),
                'received_at' => (new \DateTimeImmutable('2 minutes ago'))->format('Y-m-d H:i:s'),
                'handled_at' => (new \DateTimeImmutable('1 minute ago'))->format('Y-m-d H:i:s'),
            ]
        );

        $connection->insert(
            'messenger_monitor',
            [
                'id' => ':id_2',
                'message_uid' => 'message_uid_2',
                'class' => TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('10 minutes ago'))->format('Y-m-d H:i:s'),
                'received_at' => (new \DateTimeImmutable('7 minutes ago'))->format('Y-m-d H:i:s'),
                'handled_at' => (new \DateTimeImmutable('4 minute ago'))->format('Y-m-d H:i:s'),
            ]
        );

        $connection->insert(
            'messenger_monitor',
            [
                'id' => ':id_3',
                'message_uid' => 'message_uid_3',
                'class' => 'Another'.TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('3 minutes ago'))->format('Y-m-d H:i:s'),
                'received_at' => (new \DateTimeImmutable('2 minutes ago'))->format('Y-m-d H:i:s'),
                'handled_at' => (new \DateTimeImmutable('1 minute ago'))->format('Y-m-d H:i:s'),
            ]
        );

        // should not be part of statistics
        $connection->insert(
            'messenger_monitor',
            [
                'id' => ':id_4',
                'message_uid' => 'message_uid_2',
                'class' => TestableMessage::class,
                'dispatched_at' => (new \DateTimeImmutable('6 hours ago'))->format('Y-m-d H:i:s'),
                'received_at' => (new \DateTimeImmutable('6 hours ago'))->format('Y-m-d H:i:s'),
                'handled_at' => (new \DateTimeImmutable('6 hours ago'))->format('Y-m-d H:i:s'),
            ]
        );
    }
}
