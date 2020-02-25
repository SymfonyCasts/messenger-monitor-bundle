<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
final class MetricsPerMessageType
{
    private $class;
    private $messagesCountOnPeriod;
    private $averageWaitingTime;
    private $averageHandlingTime;

    public function __construct(string $class, int $messagesCountOnPeriod, float $averageWaitingTime, float $averageHandlingTime)
    {
        $this->class = $class;
        $this->messagesCountOnPeriod = $messagesCountOnPeriod;
        $this->averageWaitingTime = $averageWaitingTime;
        $this->averageHandlingTime = $averageHandlingTime;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMessagesCountOnPeriod(): int
    {
        return $this->messagesCountOnPeriod;
    }

    public function getAverageWaitingTime(): float
    {
        return $this->averageWaitingTime;
    }

    public function getAverageHandlingTime(): float
    {
        return $this->averageHandlingTime;
    }
}
