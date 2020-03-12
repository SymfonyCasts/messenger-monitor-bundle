<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\MessageRetriedByUserEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;

/**
 * @internal
 */
final class SaveRetriedMessageListener implements EventSubscriberInterface
{
    public function __construct(private Connection $doctrineConnection)
    {
    }

    public function onMessageRetried(MessageRetriedByUserEvent $retriedMessageEvent): void
    {
        /** @psalm-suppress PossiblyFalseArgument */
        $this->doctrineConnection->saveMessage(
            new StoredMessage(
                $retriedMessageEvent->getMessageUid(),
                $retriedMessageEvent->getMessageClass(),
                \DateTimeImmutable::createFromFormat('0.u00 U', microtime())
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageRetriedByUserEvent::class => 'onMessageRetried',
        ];
    }
}
