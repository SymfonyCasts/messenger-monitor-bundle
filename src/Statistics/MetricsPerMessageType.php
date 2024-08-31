<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
final class MetricsPerMessageType
{
    public function __construct(
        private \DateTimeImmutable $fromDate,
        private \DateTimeImmutable $toDate,
        private string $class,
        private int $messagesCountOnPeriod,
        private float $averageWaitingTime,
        private float $averageHandlingTime,
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getShortClassName(): string
    {
        return basename(str_replace('\\', '/', $this->class));
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
        return abs($this->fromDate->getTimestamp() - $this->toDate->getTimestamp()) / (60 * 60);
    }
}
