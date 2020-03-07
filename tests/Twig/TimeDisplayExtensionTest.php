<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Twig\TimeDisplayExtension;

final class TimeDisplayExtensionTest extends TestCase
{
    public function testFormatTime(): void
    {
        $timeDisplayExtension = new TimeDisplayExtension();

        $this->assertSame('10 seconds', $timeDisplayExtension->formatTime(10));

        $this->assertSame('1 second', $timeDisplayExtension->formatTime(1));
        $this->assertSame('1 second', $timeDisplayExtension->formatTime(1.004));

        $this->assertSame('0.12 seconds', $timeDisplayExtension->formatTime(0.123));
        $this->assertSame('11 seconds', $timeDisplayExtension->formatTime(11.123));

        $this->assertSame('1 minute', $timeDisplayExtension->formatTime(60.123));
        $this->assertSame('5 minutes', $timeDisplayExtension->formatTime(300.123));

        $this->assertSame('1 minute 5 seconds', $timeDisplayExtension->formatTime(65.123));
        $this->assertSame('5 minutes 1 second', $timeDisplayExtension->formatTime(301.123));
    }
}
