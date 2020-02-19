<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Exception;

use Symfony\Component\Messenger\Exception\RuntimeException;

/**
 * @internal
 */
final class FailureReceiverDoesNotExistException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('There is no failure receiver.');
    }
}
