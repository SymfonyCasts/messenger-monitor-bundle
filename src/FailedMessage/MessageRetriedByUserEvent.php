<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

/**
 * @internal
 */
final class MessageRetriedByUserEvent
{
    public function __construct(private string $messageUid, private string $messageClass)
    {
    }

    public function getMessageUid(): string
    {
        return $this->messageUid;
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }
}
