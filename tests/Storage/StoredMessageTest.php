<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Storage;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessage;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

final class StoredMessageTest extends TestCase
{
    public function testStoredMessage(): void
    {
        $storedMessage = new StoredMessage('id', TestableMessage::class, $dispatchedAt = new \DateTimeImmutable());

        $this->assertSame('id', $storedMessage->getId());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
        $this->assertSame($dispatchedAt->format('Y-m-d'), $storedMessage->getDispatchedAt()->format('Y-m-d'));
    }

    public function testCreateFromEnvelope(): void
    {
        $storedMessage = StoredMessage::fromEnvelope(
            new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()])
        );

        $this->assertSame($stamp->getId(), $storedMessage->getId());
        $this->assertSame(TestableMessage::class, $storedMessage->getMessageClass());
    }

    public function testCreateFromDatabaseRow(): void
    {
        $storedMessage = StoredMessage::fromDatabaseRow(
            [
                'id' => 'id',
                'class' => TestableMessage::class,
                'dispatched_at' => '2019-01-01 10:00:00',
                'received_at' => '2019-01-01 10:05:00',
                'handled_at' => '2019-01-01 10:10:00',
            ]
        );

        $this->assertEquals(
            new StoredMessage(
                'id',
                TestableMessage::class,
                new \DateTimeImmutable('2019-01-01 10:00:00'),
                new \DateTimeImmutable('2019-01-01 10:05:00'),
                new \DateTimeImmutable('2019-01-01 10:10:00')
            ),
            $storedMessage
        );
    }

    public function testExceptionWhenCreateFromEnvelopeWithoutStamp(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Envelope should have a MonitorIdStamp!');

        StoredMessage::fromEnvelope(new Envelope(new TestableMessage()));
    }
}
