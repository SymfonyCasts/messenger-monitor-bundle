<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\MessageRetriedByUserEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessageProvider;

/**
 * @internal
 */
final class SaveRetriedMessageListener implements EventSubscriberInterface
{
    private $doctrineConnection;
    private $storedMessageProvider;

    public function __construct(Connection $doctrineConnection, StoredMessageProvider $storedMessageProvider)
    {
        $this->doctrineConnection = $doctrineConnection;
        $this->storedMessageProvider = $storedMessageProvider;
    }

    public function onMessageRetried(WorkerMessageFailedEvent $event): void
    {
        if (!$event->willRetry()) {
            return;
        }

        // we don't want to handle messages coming from failure transport here
        // otherwise, if it fails, it would be updated before a new one is created, because of listeners priorities
        $sentToFailureTransportStamp = $event->getEnvelope()->last(SentToFailureTransportStamp::class);
        if (null !== $sentToFailureTransportStamp) {
            return;
        }

        $storedMessage = $this->storedMessageProvider->getStoredMessage($event->getEnvelope());

        if (null === $storedMessage) {
            return;
        }

        $this->doctrineConnection->saveMessage(
            new StoredMessage(
                $storedMessage->getMessageUid(),
                $storedMessage->getMessageClass(),
                \DateTimeImmutable::createFromFormat('U', (string) time())
            )
        );
    }

    public function onMessageRetriedByUser(MessageRetriedByUserEvent $retriedMessageEvent): void
    {
        $this->doctrineConnection->saveMessage(
            new StoredMessage(
                $retriedMessageEvent->getMessageUid(),
                $retriedMessageEvent->getMessageClass(),
                \DateTimeImmutable::createFromFormat('U', (string) time())
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Should have a lower priority than UpdateStoredMessageListener::onMessageFailed
            WorkerMessageFailedEvent::class => ['onMessageRetried', 20],
            // Should have a higher priority than UpdateStoredMessageListener::onMessageFailed
            MessageRetriedByUserEvent::class => ['onMessageRetriedByUser', 255],
        ];
    }
}
