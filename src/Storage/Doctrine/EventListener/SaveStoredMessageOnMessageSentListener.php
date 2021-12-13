<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Exception\MessengerIdStampMissingException;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;

/**
 * @internal
 */
final class SaveStoredMessageOnMessageSentListener implements EventSubscriberInterface
{
    public function __construct(private Connection $doctrineConnection, private ?LoggerInterface $logger = null)
    {
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        try {
            $this->doctrineConnection->saveMessage(StoredMessage::fromEnvelope($event->getEnvelope()));
        } catch (MessengerIdStampMissingException $exception) {
            $this->logger?->error($exception->getMessage());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // should happen after AddStampOnMessageSentListener
            SendMessageToTransportsEvent::class => ['onMessageSent', 10],
        ];
    }
}
