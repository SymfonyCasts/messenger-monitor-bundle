<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\MessageRetriedByUserEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener\SaveRetriedMessageListener;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessageProvider;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class SaveRetriedMessageListenerTest extends TestCase
{
    public function testOnMessageRetriedDoesNotStoreNewMessageIfComingFromFailureTransport(): void
    {
        $listener = new SaveRetriedMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $storedMessageProvider->expects($this->never())->method('getStoredMessage');

        $doctrineConnection->expects($this->never())->method('saveMessage');

        $event = new WorkerMessageFailedEvent(
            (new Envelope(new TestableMessage()))->with(new SentToFailureTransportStamp('orginalreceiverName')),
            'receiverName',
            new \Exception()
        );
        $event->setForRetry();
        $listener->onMessageRetried($event);
    }

    public function testOnMessageRetriedDoesNotStoreNewMessageIfNoOriginalMessageFound(): void
    {
        $listener = new SaveRetriedMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $storedMessageProvider->expects($this->once())->method('getStoredMessage')->willReturn(null);

        $doctrineConnection->expects($this->never())->method('saveMessage');

        $event = new WorkerMessageFailedEvent(
            new Envelope(new TestableMessage()),
            'receiverName',
            new \Exception()
        );
        $event->setForRetry();
        $listener->onMessageRetried($event);
    }

    public function testOnMessageRetriedStoredAnotherMessage(): void
    {
        $listener = new SaveRetriedMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $storedMessageProvider = $this->createMock(StoredMessageProvider::class)
        );

        $storedMessageProvider->expects($this->once())
            ->method('getStoredMessage')
            ->willReturn(
                new StoredMessage('message_uid', TestableMessage::class, new \DateTimeImmutable())
            );

        $doctrineConnection->expects($this->once())
            ->method('saveMessage')
            ->with(
                $this->callback(
                    static function (StoredMessage $storedMessage): bool {
                        return 'message_uid' === $storedMessage->getMessageUid()
                            && TestableMessage::class === $storedMessage->getMessageClass();
                    }
                )
            );

        $event = new WorkerMessageFailedEvent(new Envelope(new TestableMessage()), 'receiverName', new \Exception());
        $event->setForRetry();
        $listener->onMessageRetried($event);
    }

    public function testOnMessageRetriedByUserStoredAnotherMessage(): void
    {
        $listener = new SaveRetriedMessageListener(
            $doctrineConnection = $this->createMock(Connection::class),
            $this->createMock(StoredMessageProvider::class)
        );

        $doctrineConnection->expects($this->once())
            ->method('saveMessage')
            ->with(
                $this->callback(
                    static function (StoredMessage $storedMessage): bool {
                        return 'message_uid' === $storedMessage->getMessageUid()
                            && TestableMessage::class === $storedMessage->getMessageClass();
                    }
                )
            );

        $listener->onMessageRetriedByUser(new MessageRetriedByUserEvent('message_uid', TestableMessage::class));
    }
}
