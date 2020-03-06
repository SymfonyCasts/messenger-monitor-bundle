<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Twig;

use SymfonyCasts\MessengerMonitorBundle\Twig\TimeDisplayExtension;
use PHPUnit\Framework\TestCase;

final class TimeDisplayExtensionTest extends TestCase
{
    public function testFormatTime(): void
    {
        $timeDisplayExtension = new TimeDisplayExtension();

        $this->assertSame('10 seconds', $timeDisplayExtension->formatPrice(10));

        $this->assertSame('1 second', $timeDisplayExtension->formatPrice(1));
        $this->assertSame('1 second', $timeDisplayExtension->formatPrice(1.004));

        $this->assertSame('0.12 seconds', $timeDisplayExtension->formatPrice(0.123));
        $this->assertSame('11 seconds', $timeDisplayExtension->formatPrice(11.123));

        $this->assertSame('1 minute', $timeDisplayExtension->formatPrice(60.123));
        $this->assertSame('5 minutes', $timeDisplayExtension->formatPrice(300.123));

        $this->assertSame('1 minute 5 seconds', $timeDisplayExtension->formatPrice(65.123));
        $this->assertSame('5 minutes 1 second', $timeDisplayExtension->formatPrice(301.123));
    }
}
