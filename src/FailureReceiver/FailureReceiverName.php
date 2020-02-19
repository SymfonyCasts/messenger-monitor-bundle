<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailureReceiver;

/**
 * @internal
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
