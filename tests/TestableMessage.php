<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests;

final class TestableMessage
{
    public $willFail = false;

    public function __construct(bool $willFail = false)
    {
        $this->willFail = $willFail;
    }
}
