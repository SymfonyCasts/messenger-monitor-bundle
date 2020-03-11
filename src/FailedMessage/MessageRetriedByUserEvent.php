<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

final class MessageRetriedByUserEvent
{
    private $messageUid;
    private $messageClass;

    public function __construct(string $messageUid, string $messageClass)
    {
        $this->messageUid = $messageUid;
        $this->messageClass = $messageClass;
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
