<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
interface StatisticsProcessorInterface
{
    public function createStatistics(): Statistics;
}
