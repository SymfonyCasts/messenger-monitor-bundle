<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
interface StatisticsProcessorInterface
{
    public function createStatistics(): Statistics;
}
