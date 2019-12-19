<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use PHPUnit\Framework\TestCase;

final class FailedMessageDetailsTest extends TestCase
{
    public function testFailedMessageDetailsGetters(): void
    {
        $failedMessageDetails = new FailedMessageDetails('id', 'App\\Message\\AwesomeMessage', '2019-01-01', 'error');

        $this->assertSame('id', $failedMessageDetails->getId());
        $this->assertSame('App\\Message\\AwesomeMessage', $failedMessageDetails->getClass());
        $this->assertSame('2019-01-01', $failedMessageDetails->getFailedAt());
        $this->assertSame('error', $failedMessageDetails->getError());
    }
}
