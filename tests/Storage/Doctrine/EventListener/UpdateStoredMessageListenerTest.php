<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener\UpdateStoredMessageListener;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessageProvider;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class UpdateStoredMessageListenerTest extends TestCase
{
    public function testUpdateOnMessageReceived(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->with($envelope)
            ->willReturn($storedMessage = new StoredMessage('id', $stamp->getId(), TestableMessage::class, new \DateTimeImmutable()));

        $doctrineConnection->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageReceived(new WorkerMessageReceivedEvent($envelope, 'receiver-name'));
        $this->assertNotNull($storedMessage->getReceivedAt());
    }

    public function testUpdateOnMessageReceivedDoesNotUpdateIfNoMessageFound(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $envelope = new Envelope(new TestableMessage());

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->with($envelope)
            ->willReturn(null);

        $doctrineConnection->expects($this->never())->method('updateMessage');

        $listener->onMessageReceived(new WorkerMessageReceivedEvent($envelope, 'receiver-name'));
    }

    public function testUpdateOnMessageHandled(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->with($envelope)
            ->willReturn($storedMessage = new StoredMessage('id', $stamp->getId(), TestableMessage::class, new \DateTimeImmutable()));

        $doctrineConnection->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageHandled(new WorkerMessageHandledEvent($envelope, 'receiver-name'));
        $this->assertNotNull($storedMessage->getHandledAt());
    }

    public function testUpdateOnMessageHandledDoesNotUpdateIfNoMessageFound(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $envelope = new Envelope(new TestableMessage());

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->with($envelope)
            ->willReturn(null);

        $doctrineConnection->expects($this->never())->method('updateMessage');

        $listener->onMessageHandled(new WorkerMessageHandledEvent($envelope, 'receiver-name'));
    }

    public function testUpdateOnMessageFailed(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->with($envelope)
            ->willReturn($storedMessage = new StoredMessage('id', $stamp->getId(), TestableMessage::class, new \DateTimeImmutable()));

        $doctrineConnection->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageFailed(new WorkerMessageFailedEvent($envelope, 'receiver-name', new \Exception()));
        $this->assertNotNull($storedMessage->getFailedAt());
    }

    public function testUpdateOnMessageFailedDoesNotUpdateIfNoMessageFound(): void
    {
        $listener = new UpdateStoredMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $envelope = new Envelope(new TestableMessage());

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->with($envelope)
            ->willReturn(null);

        $doctrineConnection->expects($this->never())->method('updateMessage');

        $listener->onMessageFailed(new WorkerMessageFailedEvent($envelope, 'receiver-name', new \Exception()));
    }
}
