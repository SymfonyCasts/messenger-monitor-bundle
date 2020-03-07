<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener\SaveStoredMessageOnMessageSentListener;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class SaveStoredMessageOnMessageSentListenerTest extends TestCase
{
    public function testStoreInDoctrineOnMessageSent(): void
    {
        $listener = new SaveStoredMessageOnMessageSentListener(
            $doctrineConnection = $this->createMock(Connection::class)
        );

        $envelope = new Envelope(new TestableMessage(), [new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())
            ->method('saveMessage')
            ->with(StoredMessage::fromEnvelope($envelope));

        $listener->onMessageSent(new SendMessageToTransportsEvent($envelope));
    }
}
