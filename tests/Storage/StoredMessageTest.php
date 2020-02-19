<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Storage;

use KaroIO\MessengerMonitorBundle\Storage\StoredMessage;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;

final class StoredMessageTest extends TestCase
{
    public function testStoredMessage(): void
    {
        $storedMessage = new StoredMessage('id', TestableMessage::class, $dispatchedAt = new \DateTimeImmutable());

        $this->assertSame('id', $storedMessage->getId());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
        $this->assertSame($dispatchedAt->format('Y-m-d'), $storedMessage->getDispatchedAt()->format('Y-m-d'));
    }
}
