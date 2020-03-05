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
        $this->to   = $to;
    }

    public function add(MetricsPerMessageType $metrics): void
    {
        $messageClasses = array_map(
            static function (MetricsPerMessageType $metric): string {
                return $metric->getClass();
            },
            $this->metrics
        );

        if (in_array($metrics->getClass(), $messageClasses, true)) {
            throw new MetricsAlreadyAddedForMessageClassException($metrics->getClass());
        }

        $this->metrics[] = $metrics;
    }

    /**
     * @return MetricsPerMessageType[]
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getMessagesCount(): int
    {
        return array_sum(
            array_map(
                static function (MetricsPerMessageType $metrics) {
                    return $metrics->getMessagesCount();
                },
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
        if ($this->getMessagesCount() === 0) {
            return 0;
        }

        return round(
            array_sum(
                array_map(
                    static function (MetricsPerMessageType $metric) use ($metricName) {
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
        return abs($this->from->getTimestamp() - $this->to->getTimestamp()) / (60 * 60);
    }
}
