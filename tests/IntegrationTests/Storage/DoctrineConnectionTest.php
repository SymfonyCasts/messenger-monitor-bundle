<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\Storage;

use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\AbstractDoctrineIntegrationTests;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class DoctrineConnectionTest extends AbstractDoctrineIntegrationTests
{
    public function testSaveAndLoadMessage(): void
    {
        /** @var Connection $doctrineConnection */
        $doctrineConnection = self::$container->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection');

        $doctrineConnection->saveMessage(
            new StoredMessage('id', 'message_uid', TestableMessage::class, $dispatchedAt = (new \DateTimeImmutable())->setTime(0, 0, 0))
        );

        $storedMessage = $doctrineConnection->findMessage('id');

        $this->assertEquals(new StoredMessage('id', 'message_uid', TestableMessage::class, $dispatchedAt), $storedMessage);
    }

    public function testSaveSeveralMessages(): void
    {
        /** @var Connection $doctrineConnection */
        $doctrineConnection = self::$container->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection');

        $doctrineConnection->saveMessage(new StoredMessage('id1', 'message_uid', TestableMessage::class, new \DateTimeImmutable()));
        $doctrineConnection->saveMessage(new StoredMessage('id2', 'message_uid', TestableMessage::class, new \DateTimeImmutable()));

        $this->assertInstanceOf(StoredMessage::class, $doctrineConnection->findMessage('id1'));
        $this->assertInstanceOf(StoredMessage::class, $doctrineConnection->findMessage('id2'));
    }

    public function testUpdateMessage(): void
    {
        /** @var Connection $doctrineConnection */
        $doctrineConnection = self::$container->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection');

        $doctrineConnection->saveMessage($storedMessage = new StoredMessage('id', 'message_uid', TestableMessage::class, new \DateTimeImmutable()));
        $storedMessage->setReceivedAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $storedMessage->setHandledAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $storedMessage->setReceiverName('receiver_name');
        $doctrineConnection->updateMessage($storedMessage);

        $storedMessageLoadedFromDatabase = $doctrineConnection->findMessage('id');

        $this->assertSame(
            $storedMessage->getReceivedAt()->format('Y-m-d H:i:s'),
            $storedMessageLoadedFromDatabase->getReceivedAt()->format('Y-m-d H:i:s')
        );

        $this->assertSame(
            $storedMessage->getHandledAt()->format('Y-m-d H:i:s'),
            $storedMessageLoadedFromDatabase->getHandledAt()->format('Y-m-d H:i:s')
        );

        $this->assertSame($storedMessage->getReceiverName(), $storedMessageLoadedFromDatabase->getReceiverName());
    }
}
