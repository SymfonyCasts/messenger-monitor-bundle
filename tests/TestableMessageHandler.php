<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class TestableMessageHandler
{
    public function __invoke(TestableMessage $message)
    {
        if (true === $message->willFail) {
            $message->willFail = false;
            throw new \Exception('oops!');
        }
    }
}
