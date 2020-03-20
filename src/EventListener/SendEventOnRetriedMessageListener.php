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
    private $decorated;
    private $eventDispatcher;
    private $logger;

    public function __construct(SendFailedMessageForRetryListener $decorated, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = null)
    {
        $this->decorated = $decorated;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
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
            if (null !== $this->logger) {
                $this->logger->error('Envelope should have a MonitorIdStamp!');
            }

            return;
        }

        $this->eventDispatcher->dispatch(
            new MessageRetriedByUserEvent(
                $monitorIdStamp->getId(),
                \get_class($event->getEnvelope()->getMessage())
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
