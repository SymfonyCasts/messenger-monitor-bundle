<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessageProvider;

/**
 * @internal
 */
final class UpdateStoredMessageListener implements EventSubscriberInterface
{
    public function __construct(private Connection $doctrineConnection, private StoredMessageProvider $storedMessageProvider)
    {
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $storedMessage = $this->storedMessageProvider->getStoredMessage($event->getEnvelope());

        if (null === $storedMessage) {
            return;
        }

        $storedMessage->setReceivedAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $storedMessage->setReceiverName($event->getReceiverName());

        $this->doctrineConnection->updateMessage($storedMessage);
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $storedMessage = $this->storedMessageProvider->getStoredMessage($event->getEnvelope());

        if (null === $storedMessage) {
            return;
        }

        $storedMessage->setHandledAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $this->doctrineConnection->updateMessage($storedMessage);
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $storedMessage = $this->storedMessageProvider->getStoredMessage($event->getEnvelope());

        if (null === $storedMessage) {
            return;
        }

        $storedMessage->setFailedAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $this->doctrineConnection->updateMessage($storedMessage);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
            WorkerMessageHandledEvent::class => 'onMessageHandled',
            // Should have a higher priority than SendFailedMessageForRetryListener
            WorkerMessageFailedEvent::class => ['onMessageFailed', 150],
        ];
    }
}
