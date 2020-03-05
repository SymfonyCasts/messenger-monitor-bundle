<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
final class MetricsPerMessageType
{
    private $from;
    private $to;
    private $class;
    private $messagesCountOnPeriod;
    private $averageWaitingTime;
    private $averageHandlingTime;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to, string $class, int $messagesCountOnPeriod, float $averageWaitingTime, float $averageHandlingTime)
    {
        $this->from                  = $from;
        $this->to                    = $to;
        $this->class                 = $class;
        $this->messagesCountOnPeriod = $messagesCountOnPeriod;
        $this->averageWaitingTime    = $averageWaitingTime;
        $this->averageHandlingTime   = $averageHandlingTime;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMessagesCount(): int
    {
        return $this->messagesCountOnPeriod;
    }

    public function getMessagesHandledPerHour(): float
    {
        return round($this->getMessagesCount() / $this->getNbHoursInPeriod(), 2);
    }

    public function getAverageWaitingTime(): float
    {
        return round($this->averageWaitingTime, 2);
    }

    public function getAverageHandlingTime(): float
    {
        return round($this->averageHandlingTime, 2);
    }

    private function getNbHoursInPeriod(): float
    {
        return abs($this->from->getTimestamp() - $this->to->getTimestamp()) / (60 * 60);
    }
}
