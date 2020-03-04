<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Statistics;

use KaroIO\MessengerMonitorBundle\Statistics\MetricsAlreadyAddedForMessageClassException;
use KaroIO\MessengerMonitorBundle\Statistics\MetricsPerMessageType;
use KaroIO\MessengerMonitorBundle\Statistics\Statistics;
use PHPUnit\Framework\TestCase;

final class StatisticsTest extends TestCase
{
    private $statistics;

    public function setUp(): void
    {
        $this->statistics = new Statistics(
            new \DateTimeImmutable('24 hours ago'),
            new \DateTimeImmutable()
        );
    }

    public function testAddSeveralMetricsForSameMessageClassThrowsException(): void
    {
        $this->statistics->add(new MetricsPerMessageType('Message1', 0, 0, 0));
        $this->statistics->add(new MetricsPerMessageType('Message2', 0, 0, 0));

        $this->expectException(MetricsAlreadyAddedForMessageClassException::class);
        $this->statistics->add(new MetricsPerMessageType('Message1', 0, 0, 0));
    }

    public function testGetMessageCount(): void
    {
        $this->assertSame(0, $this->statistics->getMessagesCount());

        $this->statistics->add(new MetricsPerMessageType('Message1', 5, 0, 0));
        $this->assertSame(5, $this->statistics->getMessagesCount());

        $this->statistics->add(new MetricsPerMessageType('Message2', 10, 0, 0));
        $this->assertSame(15, $this->statistics->getMessagesCount());
    }

    public function testGetMessagesHandledPerHour(): void
    {
        $this->assertSame(0.0, $this->statistics->getMessagesHandledPerHour());

        $this->statistics->add(new MetricsPerMessageType('Message1', 24, 0, 0));
        $this->statistics->add(new MetricsPerMessageType('Message2', 48, 0, 0));
        $this->assertSame(3.0, $this->statistics->getMessagesHandledPerHour());
    }

    public function testGetAverageWaitingTime(): void
    {
        $this->assertSame(0.0, $this->statistics->getAverageWaitingTime());

        $this->statistics->add(new MetricsPerMessageType('Message1', 1, 10, 0));
        $this->statistics->add(new MetricsPerMessageType('Message2', 2, 25, 0));
        $this->assertSame(20.0, $this->statistics->getAverageWaitingTime());
    }

    public function testGetAverageHandlingTime(): void
    {
        $this->assertSame(0.0, $this->statistics->getAverageHandlingTime());

        $this->statistics->add(new MetricsPerMessageType('Message1', 1, 0, 10));
        $this->statistics->add(new MetricsPerMessageType('Message2', 2, 0, 25));
        $this->assertSame(20.0, $this->statistics->getAverageHandlingTime());
    }

    public function testGetMessagesCountPerMessageType(): void
    {
        $this->assertSame([], $this->statistics->getMessagesCountPerMessageType());

        $this->statistics->add(new MetricsPerMessageType('Message1', 1, 0, 0));
        $this->statistics->add(new MetricsPerMessageType('Message2', 2, 0, 0));
        $this->assertSame(['Message1' => 1, 'Message2' => 2], $this->statistics->getMessagesCountPerMessageType());
    }

    public function testGetMessagesHandledPerHourPerMessageType(): void
    {
        $this->assertSame([], $this->statistics->getMessagesHandledPerHourPerMessageType());

        $this->statistics->add(new MetricsPerMessageType('Message1', 48, 0, 0));
        $this->statistics->add(new MetricsPerMessageType('Message2', 24, 0, 0));
        $this->assertSame(['Message1' => 2.0, 'Message2' => 1.0], $this->statistics->getMessagesHandledPerHourPerMessageType());
    }

    public function testGetAverageWaitingTimePerMessageType(): void
    {
        $this->assertSame([], $this->statistics->getAverageWaitingTimePerMessageType());

        $this->statistics->add(new MetricsPerMessageType('Message1', 1, 10, 0));
        $this->statistics->add(new MetricsPerMessageType('Message2', 1, 20, 0));
        $this->assertSame(['Message1' => 10.0, 'Message2' => 20.0], $this->statistics->getAverageWaitingTimePerMessageType());
    }

    public function testGetAverageHandlingTimePerMessageType(): void
    {
        $this->assertSame([], $this->statistics->getAverageHandlingTimePerMessageType());

        $this->statistics->add(new MetricsPerMessageType('Message1', 1, 0, 10));
        $this->statistics->add(new MetricsPerMessageType('Message2', 1, 0, 20));
        $this->assertSame(['Message1' => 10.0, 'Message2' => 20.0], $this->statistics->getAverageHandlingTimePerMessageType());
    }
}
