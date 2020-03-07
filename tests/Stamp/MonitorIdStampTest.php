<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Stamp;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;

final class MonitorIdStampTest extends TestCase
{
    public function testMonitorIdStamp(): void
    {
        $stamp = new MonitorIdStamp();
        $this->assertSame(UUID_TYPE_RANDOM, uuid_type($stamp->getId()));
    }
}
