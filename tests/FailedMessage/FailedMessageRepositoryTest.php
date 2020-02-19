<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\FailedMessage;

use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageDetails;
use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRepository;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class FailedMessageRepositoryTest extends TestCase
{
    public function testListFailedMessages(): void
    {
        $failureReceiverProvider = $this->createMock(FailureReceiverProvider::class);
        $failedMessageRepository = new FailedMessageRepository($failureReceiverProvider);

        $failureReceiverProvider->expects($this->once())
            ->method('getFailureReceiver')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())
            ->method('all')
            ->with(10)
            ->willReturn(
                [$envelope1 = $this->createEnvelope(1), $envelope2 = $this->createEnvelope(2)]
            );

        $this->assertEquals(
            [
                new FailedMessageDetails(
                    1,
                    TestableMessage::class,
                    $envelope1->last(RedeliveryStamp::class)->getRedeliveredAt()->format('Y-m-d H:i:s'),
                    'exceptionMessage'
                ),
                new FailedMessageDetails(
                    2,
                    TestableMessage::class,
                    $envelope2->last(RedeliveryStamp::class)->getRedeliveredAt()->format('Y-m-d H:i:s'),
                    'exceptionMessage'
                ),
            ],
            $failedMessageRepository->listFailedMessages()
        );
    }

    private function createEnvelope(int $id): Envelope
    {
        return new Envelope(
            new TestableMessage(),
            [
                new TransportMessageIdStamp($id),
                new RedeliveryStamp(0, 'exceptionMessage'),
            ]
        );
    }

    public function testListFailedMessagesWithNoStamps(): void
    {
        $failureReceiverProvider = $this->createMock(FailureReceiverProvider::class);
        $failedMessageRepository = new FailedMessageRepository($failureReceiverProvider);

        $failureReceiverProvider->expects($this->once())
            ->method('getFailureReceiver')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())
            ->method('all')
            ->with(10)
            ->willReturn([new Envelope(new TestableMessage()), new Envelope(new TestableMessage())]);

        $this->assertEquals(
            [
                new FailedMessageDetails(null, TestableMessage::class, '', ''),
                new FailedMessageDetails(null, TestableMessage::class, '', ''),
            ],
            $failedMessageRepository->listFailedMessages()
        );
    }
}
