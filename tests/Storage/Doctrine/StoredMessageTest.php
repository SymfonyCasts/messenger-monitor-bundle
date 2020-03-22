<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Exception\MessengerIdStampMissingException;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class StoredMessageTest extends TestCase
{
    public function testStoredMessage(): void
    {
        $storedMessage = new StoredMessage(
            'message_uid', TestableMessage::class, $dispatchedAt = new \DateTimeImmutable(), 1, $receivedAt = new \DateTimeImmutable(), $handledAt = new \DateTimeImmutable(), $failedAt = new \DateTimeImmutable(), $receiverName = 'receiver_name'
        );

        $this->assertSame(1, $storedMessage->getId());
        $this->assertSame('message_uid', $storedMessage->getMessageUid());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
        $this->assertSame($dispatchedAt, $storedMessage->getDispatchedAt());
        $this->assertSame($receivedAt, $storedMessage->getReceivedAt());
        $this->assertSame($handledAt, $storedMessage->getHandledAt());
        $this->assertSame($failedAt, $storedMessage->getFailedAt());
        $this->assertSame($receiverName, $storedMessage->getReceiverName());
    }

    public function testCreateFromEnvelope(): void
    {
        $storedMessage = StoredMessage::fromEnvelope(
            new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()])
        );

        $this->assertSame($stamp->getId(), $storedMessage->getMessageUid());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
    }

    public function testExceptionWhenCreateFromEnvelopeWithoutStamp(): void
    {
        $this->expectException(MessengerIdStampMissingException::class);
        $this->expectExceptionMessage('Envelope should have a MonitorIdStamp!');

        StoredMessage::fromEnvelope(new Envelope(new TestableMessage()));
    }
}
