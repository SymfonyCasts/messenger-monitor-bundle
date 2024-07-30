<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Statistics;

final class MetricsAlreadyAddedForMessageClassException extends \RuntimeException
{
    public function __construct(string $messageClass)
    {
        parent::__construct(\sprintf('Metrics already added for message class "%s"', $messageClass));
    }
}
