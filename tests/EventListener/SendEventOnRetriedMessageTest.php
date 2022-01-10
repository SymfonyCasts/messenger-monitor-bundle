<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use SymfonyCasts\MessengerMonitorBundle\EventListener\SendEventOnRetriedMessageListener;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\MessageRetriedByUserEvent;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

final class SendEventOnRetriedMessageTest extends TestCase
{
    public function testOnMessageFailedDoesNotDispatchEventIfWillNotRetryEvent(): void
    {
        $sendEventOnRetriedMessage = new SendEventOnRetriedMessageListener(
            $sendFailedMessageForRetryListener = $this->createMock(SendFailedMessageForRetryListener::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $event = new WorkerMessageFailedEvent(
            new Envelope(new TestableMessage()),
            'receiverName',
            new \Exception('oops')
        );

        $sendFailedMessageForRetryListener->expects($this->once())->method('onMessageFailed')->with($event);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $sendEventOnRetriedMessage->onMessageFailed($event);
    }

    public function testOnMessageFailedDoesNotDispatchEventIfEnvelopeIsSentFromFailureTransport(): void
    {
        $sendEventOnRetriedMessage = new SendEventOnRetriedMessageListener(
            $sendFailedMessageForRetryListener = $this->createMock(SendFailedMessageForRetryListener::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $event = new WorkerMessageFailedEvent(
            new Envelope(new TestableMessage(), [new SentToFailureTransportStamp('originalReceiverName')]),
            'receiverName',
            new \Exception('oops')
        );
        $event->setForRetry();

        $sendFailedMessageForRetryListener->expects($this->once())->method('onMessageFailed')->with($event);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $sendEventOnRetriedMessage->onMessageFailed($event);
    }

    public function testOnMessageFailedDoesNotDispatchEventIfEnvelopeDoesNotHaveMonitorIdStamp(): void
    {
        $sendEventOnRetriedMessage = new SendEventOnRetriedMessageListener(
            $sendFailedMessageForRetryListener = $this->createMock(SendFailedMessageForRetryListener::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $event = new WorkerMessageFailedEvent(
            new Envelope(new TestableMessage()),
            'receiverName',
            new \Exception('oops')
        );
        $event->setForRetry();

        $sendFailedMessageForRetryListener->expects($this->once())->method('onMessageFailed')->with($event);
        $logger->expects($this->once())->method('error')->with('Envelope should have a MonitorIdStamp!');
        $eventDispatcher->expects($this->never())->method('dispatch');

        $sendEventOnRetriedMessage->onMessageFailed($event);
    }

    public function testOnMessageFailedDispatchesEvent(): void
    {
        $sendEventOnRetriedMessage = new SendEventOnRetriedMessageListener(
            $sendFailedMessageForRetryListener = $this->createMock(SendFailedMessageForRetryListener::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $event = new WorkerMessageFailedEvent(
            new Envelope(new TestableMessage(), [$monitorIdStamp = new MonitorIdStamp()]),
            'receiverName',
            new \Exception('oops')
        );
        $event->setForRetry();

        $sendFailedMessageForRetryListener->expects($this->once())->method('onMessageFailed')->with($event);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                new MessageRetriedByUserEvent(
                    $monitorIdStamp->getId(),
                    TestableMessage::class
                )
            );

        $sendEventOnRetriedMessage->onMessageFailed($event);
    }
}
