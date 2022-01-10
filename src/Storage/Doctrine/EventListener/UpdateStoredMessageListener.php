<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Stamp\DelayStamp;
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

        /** @var DelayStamp $delayStamp */
        $delayStamp = $event->getEnvelope()->last(DelayStamp::class) ?? new DelayStamp(0);

        $storedMessage->updateWaitingTime($delayStamp->getDelay() / 1000);
        $storedMessage->setReceiverName($event->getReceiverName());

        $this->doctrineConnection->updateMessage($storedMessage);
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $storedMessage = $this->storedMessageProvider->getStoredMessage($event->getEnvelope());

        if (null === $storedMessage) {
            return;
        }

        $storedMessage->updateHandlingTime();
        $this->doctrineConnection->updateMessage($storedMessage);
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $storedMessage = $this->storedMessageProvider->getStoredMessage($event->getEnvelope());

        if (null === $storedMessage) {
            return;
        }

        $storedMessage->updateFailingTime();
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
