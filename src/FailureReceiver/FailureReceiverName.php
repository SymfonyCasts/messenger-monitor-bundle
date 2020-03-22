<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailureReceiver;

/**
 * @internal
 * @psalm-immutable
 */
final class FailureReceiverName
{
    private $failureReceiverName;

    public function __construct(?string $failureReceiverName)
    {
        $this->failureReceiverName = $failureReceiverName;
    }

    public function toString(): ?string
    {
        return $this->failureReceiverName;
    }
}
