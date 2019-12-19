<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailureReceiver;

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
        $failureReceiverName = new FailureReceiverName();
        $this->assertNull($failureReceiverName->toString());
    }
}
