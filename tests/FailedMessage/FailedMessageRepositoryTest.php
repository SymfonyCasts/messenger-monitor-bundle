<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use KaroIO\MessengerMonitorBundle\Test\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class FailedMessageRepositoryTest extends TestCase
{
    public function testListFailedMessages(): void
    {
        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failedMessageRepository = new FailedMessageRepository($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
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
                    Message::class,
                    $envelope1->last(RedeliveryStamp::class)->getRedeliveredAt()->format('Y-m-d H:i:s'),
                    'exceptionMessage'
                ),
                new FailedMessageDetails(
                    2,
                    Message::class,
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
            new Message(),
            [
                new TransportMessageIdStamp($id),
                new RedeliveryStamp(0, 'exceptionMessage')
            ]
        );
    }

    public function testListFailedMessagesWithNoStamps()
    {

        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failedMessageRepository = new FailedMessageRepository($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())
            ->method('all')
            ->with(10)
            ->willReturn([new Envelope(new Message()), new Envelope(new Message())]);

        $this->assertEquals(
            [
                new FailedMessageDetails(null, Message::class, '', ''),
                new FailedMessageDetails(null, Message::class, '', ''),
            ],
            $failedMessageRepository->listFailedMessages()
        );
    }
}
