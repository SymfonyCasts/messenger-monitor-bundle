<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures;

abstract class Message
{
    abstract public function shouldFail(): bool;
}
