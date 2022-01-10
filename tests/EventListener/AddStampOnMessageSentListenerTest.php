<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use SymfonyCasts\MessengerMonitorBundle\EventListener\AddStampOnMessageSentListener;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

final class AddStampOnMessageSentListenerTest extends TestCase
{
    public function testAddStampOnMessageSent(): void
    {
        $listener = new AddStampOnMessageSentListener();
        $listener->onMessageSent($event = new SendMessageToTransportsEvent(new Envelope(new TestableMessage())));

        $this->assertNotNull($event->getEnvelope()->last(MonitorIdStamp::class));
    }
}
