<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures;

final class RetryableMessage extends Message
{
    public $failures = 2;

    public function shouldFail(): bool
    {
        if ($this->failures > 0) {
            --$this->failures;

            return true;
        }

        return false;
    }
}
