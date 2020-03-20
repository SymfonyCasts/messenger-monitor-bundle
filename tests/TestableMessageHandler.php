<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class TestableMessageHandler implements MessageHandlerInterface
{
    public function __invoke(TestableMessage $message)
    {
        if (true === $message->willFail) {
            $message->willFail = false;
            throw new \Exception('oops!');
        }
    }
}
