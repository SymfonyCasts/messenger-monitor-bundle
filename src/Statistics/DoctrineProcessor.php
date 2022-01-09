<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Statistics;

use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;

/**
 * @internal
 */
final class DoctrineProcessor implements StatisticsProcessorInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function createStatistics(): Statistics
    {
        return $this->connection->getStatistics(
            new \DateTimeImmutable('24 hours ago'),
            new \DateTimeImmutable()
        );
    }
}
