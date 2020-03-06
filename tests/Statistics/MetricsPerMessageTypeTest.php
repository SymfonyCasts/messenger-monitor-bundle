<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Statistics;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Statistics\MetricsPerMessageType;

final class MetricsPerMessageTypeTest extends TestCase
{
    public function testGetClass(): void
    {
        $metrics = new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message', 0, 0, 0);
        $this->assertSame('Message', $metrics->getClass());
    }

    public function testMessagesCount(): void
    {
        $metrics = new MetricsPerMessageType(new \DateTimeImmutable(), new \DateTimeImmutable(), 'Message', 10, 0, 0);
        $this->assertSame(10, $metrics->getMessagesCount());
    }

    public function testMessagesHandledPerHour(): void
    {
        $metrics = new MetricsPerMessageType(new \DateTimeImmutable('24 hours ago'), new \DateTimeImmutable(), 'Message', 24, 0, 0);
        $this->assertSame(1.0, $metrics->getMessagesHandledPerHour());
    }

    public function testAverageWaitingTime(): void
    {
        $metrics = new MetricsPerMessageType(new \DateTimeImmutable('24 hours ago'), new \DateTimeImmutable(), 'Message', 24, 10, 0);
        $this->assertSame(10.0, $metrics->getAverageWaitingTime());
    }

    public function testAverageHandlingTime(): void
    {
        $metrics = new MetricsPerMessageType(new \DateTimeImmutable('24 hours ago'), new \DateTimeImmutable(), 'Message', 24, 0, 10);
        $this->assertSame(10.0, $metrics->getAverageHandlingTime());
    }
}
