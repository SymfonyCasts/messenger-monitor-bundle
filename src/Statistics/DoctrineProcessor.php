<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Statistics;

use KaroIO\MessengerMonitorBundle\Storage\Doctrine\Connection;

/**
 * @internal
 */
final class DoctrineProcessor implements StatisticsProcessorInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /** {@inheritdoc} */
    public function createStatistics(): Statistics
    {
        // todo: this period should be chosen by user
        return $this->connection->getStatistics(
            new \DateTimeImmutable('24 hours ago'),
            new \DateTimeImmutable()
        );
    }
}
