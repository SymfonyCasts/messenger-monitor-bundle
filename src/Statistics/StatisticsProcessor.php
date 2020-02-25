<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
interface StatisticsProcessor
{
    public function processStatistics(): Statistics;
}
