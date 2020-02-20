<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Stamp;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use PHPUnit\Framework\TestCase;

final class MonitorIdStampTest extends TestCase
{
    public function testMonitorIdStamp(): void
    {
        $stamp = new MonitorIdStamp();
        $this->assertSame(UUID_TYPE_RANDOM, uuid_type($stamp->getId()));
    }
}
