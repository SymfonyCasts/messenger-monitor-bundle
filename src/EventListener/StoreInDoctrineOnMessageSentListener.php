<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\EventListener;

use KaroIO\MessengerMonitorBundle\Storage\StoredMessage;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

/**
 * @internal
 */
final class StoreInDoctrineOnMessageSentListener implements EventSubscriberInterface
{
    private $storedMessageRepository;

    public function __construct(StoredMessageRepository $storedMessageRepository)
    {
        $this->storedMessageRepository = $storedMessageRepository;
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        $this->storedMessageRepository->saveMessage(StoredMessage::fromEnvelope($event->getEnvelope()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // should happen after AddStampOnMessageSentListener
            SendMessageToTransportsEvent::class => ['onMessageSent', 1],
        ];
    }
}
