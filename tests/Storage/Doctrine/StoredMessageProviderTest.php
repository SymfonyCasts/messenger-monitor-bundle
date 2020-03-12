<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessageProvider;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

final class StoredMessageProviderTest extends TestCase
{
    public function testGetStoredMessageLogsAnErrorWhenMessageDoesNotHaveMonitorIdStamp(): void
    {
        $storedMessageProvider = new StoredMessageProvider(
            $doctrineConnection = $this->createMock(Connection::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $doctrineConnection->expects($this->never())->method('findMessage');
        $logger->expects($this->once())->method('error')->with('Envelope should have a MonitorIdStamp!');

        $this->assertNull($storedMessageProvider->getStoredMessage(new Envelope(new TestableMessage())));
    }

    public function testGetStoredMessageLogsAnErrorWhenStoredMessageNotFound(): void
    {
        $storedMessageProvider = new StoredMessageProvider(
            $doctrineConnection = $this->createMock(Connection::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())->method('findMessage')->with($stamp->getId())->willReturn(null);
        $logger->expects($this->once())->method('error')->with(sprintf('Message with id "%s" not found', $stamp->getId()));

        $this->assertNull($storedMessageProvider->getStoredMessage($envelope));
    }

    public function testGetStoredMessage(): void
    {
        $storedMessageProvider = new StoredMessageProvider(
            $doctrineConnection = $this->createMock(Connection::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())
            ->method('findMessage')
            ->with($stamp->getId())
            ->willReturn($storedMessage = StoredMessage::fromEnvelope($envelope));

        $logger->expects($this->never())->method('error');

        $this->assertSame($storedMessage, $storedMessageProvider->getStoredMessage($envelope));
    }
}
