<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\RetriedMessageEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;

/**
 * @internal
 */
final class SaveRetriedMessageListener implements EventSubscriberInterface
{
    private $doctrineConnection;

    public function __construct(Connection $doctrineConnection)
    {
        $this->doctrineConnection = $doctrineConnection;
    }

    public function onMessageRetried(RetriedMessageEvent $retriedMessageEvent): void
    {
        $this->doctrineConnection->saveMessage(
            new StoredMessage(
                uuid_create(UUID_TYPE_RANDOM),
                $retriedMessageEvent->getMessageUid(),
                $retriedMessageEvent->getMessageClass(),
                \DateTimeImmutable::createFromFormat('U', (string) time())
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RetriedMessageEvent::class => 'onMessageRetried',
        ];
    }
}
