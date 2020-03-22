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
    private $doctrineConnection;
    private $logger;

    public function __construct(Connection $doctrineConnection, LoggerInterface $logger = null)
    {
        $this->doctrineConnection = $doctrineConnection;
        $this->logger = $logger;
    }

    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        try {
            $this->doctrineConnection->saveMessage(StoredMessage::fromEnvelope($event->getEnvelope()));
        } catch (MessengerIdStampMissingException $exception) {
            if (null !== $this->logger) {
                $this->logger->error($exception->getMessage());
            }
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
