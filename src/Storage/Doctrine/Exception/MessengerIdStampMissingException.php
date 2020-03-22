<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Exception;

use Symfony\Component\Messenger\Exception\RuntimeException;

/**
 * @internal
 */
final class MessengerIdStampMissingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Envelope should have a MonitorIdStamp!');
    }
}
