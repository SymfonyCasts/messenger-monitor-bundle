<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener\UpdateStoredMessageListener;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class UpdateStoredMessageListenerTest extends TestCase
{
    public function testUpdateOnMessageReceived(): void
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

    public function testUpdateOnMessageReceivedLogsAnErrorWhenMessageDoesNotHaveMonitorIdStamp(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $envelope = new Envelope(new TestableMessage());

        $doctrineConnection->expects($this->never())->method('findMessage');
        $logger->expects($this->once())->method('error')->with('Envelope should have a MonitorIdStamp!');

        $listener->onMessageReceived(new WorkerMessageReceivedEvent($envelope, 'receiver-name'));
    }

    public function testUpdateOnMessageReceivedLogsAnErrorWhenStoredMessageNotFound(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())->method('findMessage')->with($stamp->getId())->willReturn(null);
        $doctrineConnection->expects($this->never())->method('updateMessage');

        $logger->expects($this->once())->method('error')->with(sprintf('Message with id "%s" not found', $stamp->getId()));

        $listener->onMessageReceived(new WorkerMessageReceivedEvent($envelope, 'receiver-name'));
    }

    public function testUpdateOnMessageHandled(): void
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
