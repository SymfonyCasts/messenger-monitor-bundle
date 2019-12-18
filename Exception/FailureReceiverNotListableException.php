<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Exception;

use Symfony\Component\Messenger\Exception\RuntimeException;

/**
 * @internal
 */
class FailureReceiverNotListableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The failure receiver does not support listing or showing specific messages.');
    }
}
