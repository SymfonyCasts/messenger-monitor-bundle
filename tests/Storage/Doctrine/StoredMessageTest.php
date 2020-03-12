<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Messenger\Envelope;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Exception\MessengerIdStampMissingException;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

/** @group time-sensitive */
final class StoredMessageTest extends TestCase
{
    public function testStoredMessage(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        $storedMessage = new StoredMessage(
            $uuid,
            TestableMessage::class,
            $dispatchedAt = new \DateTimeImmutable(),
            1,
            $waitingTime = 0.1,
            $receiverName = 'receiver_name',
            $handlingTime = 0.2,
            $failingTime = 0.3
        );

        $this->assertSame(1, $storedMessage->getId());
        $this->assertSame($uuid, $storedMessage->getMessageUid());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
        $this->assertSame($dispatchedAt, $storedMessage->getDispatchedAt());
        $this->assertSame($waitingTime, $storedMessage->getWaitingTime());
        $this->assertSame($receiverName, $storedMessage->getReceiverName());
        $this->assertSame($handlingTime, $storedMessage->getHandlingTime());
        $this->assertSame($failingTime, $storedMessage->getFailingTime());
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

    public function testUpdateWaitingTime(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        ClockMock::register(StoredMessage::class);
        ClockMock::withClockMock((new \DateTimeImmutable('2020-01-01 00:00:01.123456'))->format('U.u'));

        $storedMessage = new StoredMessage($uuid, TestableMessage::class, new \DateTimeImmutable('2020-01-01 00:00:00.000'));

        $storedMessage->updateWaitingTime();
        $this->assertSame(1.123456, $storedMessage->getWaitingTime());
    }

    public function testUpdateWaitingTimeWithOffset(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        ClockMock::register(StoredMessage::class);
        ClockMock::withClockMock((new \DateTimeImmutable('2020-01-01 00:00:03.123456'))->format('U.u'));

        $storedMessage = new StoredMessage($uuid, TestableMessage::class, new \DateTimeImmutable('2020-01-01 00:00:00.000'));

        $storedMessage->updateWaitingTime(2);
        $this->assertSame(1.123456, $storedMessage->getWaitingTime());
    }

    public function testUpdateHandlingTime(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        ClockMock::register(StoredMessage::class);
        ClockMock::withClockMock((new \DateTimeImmutable('2020-01-01 00:00:02.123456'))->format('U.u'));

        $storedMessage = new StoredMessage($uuid, TestableMessage::class, new \DateTimeImmutable('2020-01-01 00:00:00.000'), 1, 1.0);

        $storedMessage->updateHandlingTime();
        $this->assertSame(1.123456, $storedMessage->getHandlingTime());
    }

    public function testUpdateFailingTime(): void
    {
        $uuid = uuid_create(UUID_TYPE_RANDOM);

        ClockMock::register(StoredMessage::class);
        ClockMock::withClockMock((new \DateTimeImmutable('2020-01-01 00:00:02.123456'))->format('U.u'));

        $storedMessage = new StoredMessage($uuid, TestableMessage::class, new \DateTimeImmutable('2020-01-01 00:00:00.000'), 1, 1.0);

        $storedMessage->updateFailingTime();
        $this->assertSame(1.123456, $storedMessage->getFailingTime());
    }
}
