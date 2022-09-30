<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailureReceiver;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class FailureReceiverName
{
    public function __construct(private ?string $failureReceiverName)
    {
    }

    public function toString(): ?string
    {
        return $this->failureReceiverName;
    }
}
