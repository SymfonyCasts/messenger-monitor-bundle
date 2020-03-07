<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Exception;

use Symfony\Component\Messenger\Exception\RuntimeException;

/**
 * @internal
 */
final class FailureReceiverNotListableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The failure receiver does not support listing or showing specific messages.');
    }
}
