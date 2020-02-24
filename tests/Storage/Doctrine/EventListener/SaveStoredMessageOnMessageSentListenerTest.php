<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\Connection;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\EventListener\SaveStoredMessageOnMessageSentListener;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

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
