<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Statistics;

/**
 * @internal
 */
final class Statistics
{
    /** @var array<string, MetricsPerMessageType> */
    private array $metrics = [];

    public function __construct(private \DateTimeImmutable $fromDate, private \DateTimeImmutable $toDate)
    {
    }

    public function add(MetricsPerMessageType $metrics): void
    {
        if (\array_key_exists($metrics->getClass(), $this->metrics)) {
            throw new MetricsAlreadyAddedForMessageClassException($metrics->getClass());
        }

        $this->metrics[$metrics->getClass()] = $metrics;
    }

    /**
     * @return array<string, MetricsPerMessageType>
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getMessagesCount(): int
    {
        return array_sum(
            array_map(
                static fn (MetricsPerMessageType $metrics) => $metrics->getMessagesCount(),
                $this->metrics
            )
        );
    }

    public function getMessagesHandledPerHour(): float
    {
        return round($this->getMessagesCount() / $this->getNbHoursInPeriod(), 2);
    }

    public function getAverageWaitingTime(): float
    {
        return $this->computeOverallAverageFor('AverageWaitingTime');
    }

    public function getAverageHandlingTime(): float
    {
        return $this->computeOverallAverageFor('AverageHandlingTime');
    }

    private function computeOverallAverageFor(string $metricName): float
    {
        if (0 === $this->getMessagesCount()) {
            return 0;
        }

        return round(
            array_sum(
                array_map(
                    static function (MetricsPerMessageType $metric) use ($metricName): float {
                        $method = 'get'.$metricName;

                        return $metric->getMessagesCount() * $metric->$method();
                    },
                    $this->metrics
                )
            ) / $this->getMessagesCount(),
            2
        );
    }

    private function getNbHoursInPeriod(): float
    {
        return abs($this->fromDate->getTimestamp() - $this->toDate->getTimestamp()) / (60 * 60);
    }
}
