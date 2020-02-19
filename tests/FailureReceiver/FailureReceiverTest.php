<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\FailureReceiver;

use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use PHPUnit\Framework\TestCase;

final class FailureReceiverTest extends TestCase
{
    public function testFailureReceiverNameWithString(): void
    {
        $failureReceiverName = new FailureReceiverName('foo');
        $this->assertSame('foo', $failureReceiverName->toString());
    }

    public function testFailureReceiverNameWithEmptyName(): void
    {
        $failureReceiverName = new FailureReceiverName(null);
        $this->assertNull($failureReceiverName->toString());
    }
}
