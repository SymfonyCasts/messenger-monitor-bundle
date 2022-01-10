<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class TestableMessageHandler implements MessageHandlerInterface
{
    public function __invoke(Message $message)
    {
        if (true === $message->shouldFail()) {
            throw new \Exception('oops!');
        }
    }
}
