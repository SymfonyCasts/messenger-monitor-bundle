<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;

/**
 * @internal
 */
final class SaveStoredMessageOnMessageSentListener implements EventSubscriberInterface
{
    private $doctrineConnection;

    public function __construct(Connection $doctrineConnection)
    {
        $this->doctrineConnection = $doctrineConnection;
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        $this->doctrineConnection->saveMessage(StoredMessage::fromEnvelope($event->getEnvelope()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // should happen after AddStampOnMessageSentListener
            SendMessageToTransportsEvent::class => ['onMessageSent', 10],
        ];
    }
}
