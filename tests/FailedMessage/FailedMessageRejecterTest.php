<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FailedMessage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\FailedMessageRejecter;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

class FailedMessageRejecterTest extends TestCase
{
    public function testRejectFailedMessage(): void
    {
        $failureReceiverProvider = $this->createMock(FailureReceiverProvider::class);
        $failedMessageRejecter = new FailedMessageRejecter($failureReceiverProvider);

        $failureReceiverProvider->expects($this->once())
            ->method('getFailureReceiver')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($envelope = new Envelope(new TestableMessage()));

        $failureReceiver->expects($this->once())->method('reject')->with($envelope);

        $failedMessageRejecter->rejectFailedMessage(1);
    }

    public function testExceptionIfNoMessageForId(): void
    {
        $this->expectException(\RuntimeException::class);

        $failureReceiverProvider = $this->createMock(FailureReceiverProvider::class);
        $failedMessageRejecter = new FailedMessageRejecter($failureReceiverProvider);

        $failureReceiverProvider->expects($this->once())
            ->method('getFailureReceiver')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $failureReceiver->expects($this->once())->method('find')->with(1)->willReturn(null);

        $failedMessageRejecter->rejectFailedMessage(1);
    }
}
