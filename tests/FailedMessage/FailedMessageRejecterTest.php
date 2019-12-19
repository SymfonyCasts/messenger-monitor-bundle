<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use KaroIO\MessengerMonitorBundle\Test\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class FailedMessageRejecterTest extends TestCase
{
    public function testRejectFailedMessage(): void
    {
        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failedMessageRejecter = new FailedMessageRejecter($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())
            ->method('find')
            ->with('id')
            ->willReturn($envelope = new Envelope(new Message()));

        $failureReceiver->expects($this->once())->method('reject')->with($envelope);

        $failedMessageRejecter->rejectFailedMessage('id');
    }

    public function testExceptionWithNoReceiverName(): void
    {
        $this->expectException(FailureReceiverDoesNotExistException::class);

        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failedMessageRejecter = new FailedMessageRejecter($receiverLocator, new FailureReceiverName());

        $failedMessageRejecter->rejectFailedMessage('id');
    }

    public function testExceptionWithNoReceiverNotListable(): void
    {
        $this->expectException(FailureReceiverNotListableException::class);

        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failedMessageRejecter = new FailedMessageRejecter($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
            ->willReturn($this->createMock(ReceiverInterface::class));

        $failedMessageRejecter->rejectFailedMessage('id');
    }

    public function testExceptionIfNoMessageForId(): void
    {
        $this->expectException(\RuntimeException::class);

        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failedMessageRejecter = new FailedMessageRejecter($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())->method('find')->with('id')->willReturn(null);

        $failedMessageRejecter->rejectFailedMessage('id');
    }
}
