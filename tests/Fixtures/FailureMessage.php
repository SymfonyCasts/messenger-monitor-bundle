<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures;

final class FailureMessage extends Message
{
    private $willFail = true;

    public function shouldFail(): bool
    {
        if ($this->willFail) {
            $this->willFail = false;

            return true;
        }

        return $this->willFail;
    }
}
