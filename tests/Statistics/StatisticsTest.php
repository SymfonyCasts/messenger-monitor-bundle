<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Statistics;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Statistics\MetricsAlreadyAddedForMessageClassException;
use SymfonyCasts\MessengerMonitorBundle\Statistics\MetricsPerMessageType;
use SymfonyCasts\MessengerMonitorBundle\Statistics\Statistics;

final class StatisticsTest extends TestCase
{
    private $statistics;

    protected function setUp(): void
    {
        $this->statistics = new Statistics(
            new \DateTimeImmutable('24 hours ago'),
            new \DateTimeImmutable()
        );
    }

    public function testAddSeveralMetricsForSameMessageClassThrowsException(): void
    {
        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message1', 0, 0, 0));
        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message2', 0, 0, 0));

        $this->expectException(MetricsAlreadyAddedForMessageClassException::class);
        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message1', 0, 0, 0));
    }

    public function testGetMessageCount(): void
    {
        $this->assertSame(0, $this->statistics->getMessagesCount());

        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message1', 5, 0, 0));
        $this->assertSame(5, $this->statistics->getMessagesCount());

        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message2', 10, 0, 0));
        $this->assertSame(15, $this->statistics->getMessagesCount());
    }

    public function testGetMessagesHandledPerHour(): void
    {
        $this->assertSame(0.0, $this->statistics->getMessagesHandledPerHour());

        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message1', 24, 0, 0));
        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message2', 48, 0, 0));
        $this->assertSame(3.0, $this->statistics->getMessagesHandledPerHour());
    }

    public function testGetAverageWaitingTime(): void
    {
        $this->assertSame(0.0, $this->statistics->getAverageWaitingTime());

        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message1', 1, 10, 0));
        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message2', 2, 25, 0));
        $this->assertSame(20.0, $this->statistics->getAverageWaitingTime());
    }

    public function testGetAverageHandlingTime(): void
    {
        $this->assertSame(0.0, $this->statistics->getAverageHandlingTime());

        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message1', 1, 0, 10));
        $this->statistics->add(new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message2', 2, 0, 25));
        $this->assertSame(20.0, $this->statistics->getAverageHandlingTime());
    }
}
