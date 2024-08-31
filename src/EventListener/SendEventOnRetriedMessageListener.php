<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\MessageRetriedByUserEvent;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;

/**
 * @internal
 *
 * This listener decorates symfony/messenger's core SendFailedMessageForRetryListener, in order to dispatch an event
 * if a message was retried by a RetryStrategy
 */
final class SendEventOnRetriedMessageListener implements EventSubscriberInterface
{
    public function __construct(
        private SendFailedMessageForRetryListener $decorated,
        private EventDispatcherInterface $eventDispatcher,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $this->decorated->onMessageFailed($event);

        if (!$event->willRetry()) {
            return;
        }

        // we don't want to handle messages coming from failure transport here
        $sentToFailureTransportStamp = $event->getEnvelope()->last(SentToFailureTransportStamp::class);
        if (null !== $sentToFailureTransportStamp) {
            return;
        }

        /** @var MonitorIdStamp|null $monitorIdStamp */
        $monitorIdStamp = $event->getEnvelope()->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            $this->logger?->error('Envelope should have a MonitorIdStamp!');

            return;
        }

        $this->eventDispatcher->dispatch(
            new MessageRetriedByUserEvent(
                $monitorIdStamp->getId(),
                $event->getEnvelope()->getMessage()::class
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must have higher priority than SendFailedMessageToFailureTransportListener
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100],
        ];
    }
}
