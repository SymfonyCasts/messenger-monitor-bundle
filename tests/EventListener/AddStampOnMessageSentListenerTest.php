<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\EventListener;

use KaroIO\MessengerMonitorBundle\EventListener\AddStampOnMessageSentListener;
use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

final class AddStampOnMessageSentListenerTest extends TestCase
{
    public function testAddStampOnMessageSent(): void
    {
        $listener = new AddStampOnMessageSentListener();
        $listener->onMessageSent($event = new SendMessageToTransportsEvent(new Envelope(new TestableMessage())));

        $this->assertNotNull($event->getEnvelope()->last(MonitorIdStamp::class));
    }
}
