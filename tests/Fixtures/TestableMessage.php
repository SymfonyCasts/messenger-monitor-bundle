<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures;

final class TestableMessage extends Message
{
    public function shouldFail(): bool
    {
        return false;
    }
}
