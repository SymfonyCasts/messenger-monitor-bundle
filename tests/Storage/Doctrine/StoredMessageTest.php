<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Storage\Doctrine;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

final class StoredMessageTest extends TestCase
{
    public function testStoredMessage(): void
    {
        $storedMessage = new StoredMessage(
            'id',
            TestableMessage::class,
            $dispatchedAt = new \DateTimeImmutable(),
            $receivedAt = new \DateTimeImmutable(),
            $handledAt = new \DateTimeImmutable(),
            $receiverName = 'receiver_name'
        );

        $this->assertSame('id', $storedMessage->getId());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
        $this->assertSame($dispatchedAt, $storedMessage->getDispatchedAt());
        $this->assertSame($receivedAt, $storedMessage->getReceivedAt());
        $this->assertSame($handledAt, $storedMessage->getHandledAt());
        $this->assertSame($receiverName, $storedMessage->getReceiverName());
    }

    public function testCreateFromEnvelope(): void
    {
        $storedMessage = StoredMessage::fromEnvelope(
            new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()])
        );

        $this->assertSame($stamp->getId(), $storedMessage->getId());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
    }

    public function testExceptionWhenCreateFromEnvelopeWithoutStamp(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Envelope should have a MonitorIdStamp!');

        StoredMessage::fromEnvelope(new Envelope(new TestableMessage()));
    }
}
