<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\Connection;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\EventListener\UpdateStoredMessageListener;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class UpdateInDoctrineListenerTest extends TestCase
{
    public function testUpdateInDoctrineOnMessageReceived(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())
            ->method('findMessage')
            ->with($stamp->getId())
            ->willReturn($storedMessage = new StoredMessage($stamp->getId(), TestableMessage::class, new \DateTimeImmutable()));

        $doctrineConnection->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageReceived(new WorkerMessageReceivedEvent($envelope, 'receiver-name'));
        $this->assertNotNull($storedMessage->getReceivedAt());
    }

    public function testUpdateInDoctrineOnMessageHandled(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())
            ->method('findMessage')
            ->with($stamp->getId())
            ->willReturn($storedMessage = new StoredMessage($stamp->getId(), TestableMessage::class, new \DateTimeImmutable(), new \DateTimeImmutable()));

        $doctrineConnection->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageHandled(new WorkerMessageHandledEvent($envelope, 'receiver-name'));
        $this->assertNotNull($storedMessage->getHandledAt());
    }
}
