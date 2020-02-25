<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
final class Statistics
{
    private $from;
    private $to;

    /** @var MetricsPerMessageType[] */
    private $metrics = [];

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function add(MetricsPerMessageType $metrics): void
    {
        $this->metrics[] = $metrics;
    }

    // statistics overall messages types
    // public function getMessagesCountOnPeriod(): int
    // public function getMessagesHandledPerHourOnPeriod(): float
    // public function getAverageWaitingTime(): float
    // public function getAverageHandlingTime(): float

    // statistics per messages type
    // public function getMessagesCountOnPeriodPerMessageType(): int[]
    // public function getMessagesHandledPerHourOnPeriodPerMessageType(): float[]
    // public function getAverageWaitingTimePerMessageType(): float[]
    // public function getAverageHandlingTimePerMessageType(): float[]
}
