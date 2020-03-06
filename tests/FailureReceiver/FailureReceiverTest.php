<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FailureReceiver;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;

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
