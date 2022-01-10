<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FailureReceiver;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use SymfonyCasts\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;
use SymfonyCasts\MessengerMonitorBundle\Locator\ReceiverLocator;

final class FailureReceiverProviderTest extends TestCase
{
    public function testGetFailureProvider(): void
    {
        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failureReceiverProvider = new FailureReceiverProvider($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
            ->willReturn($failureReceiver = $this->createMock(ListableReceiverInterface::class));

        $this->assertSame($failureReceiver, $failureReceiverProvider->getFailureReceiver());
    }

    public function testExceptionWithNoReceiverNotListable(): void
    {
        $this->expectException(FailureReceiverNotListableException::class);

        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failureReceiverProvider = new FailureReceiverProvider($receiverLocator, new FailureReceiverName('failed'));

        $receiverLocator->expects($this->once())
            ->method('getReceiver')
            ->with('failed')
            ->willReturn($this->createMock(ReceiverInterface::class));

        $failureReceiverProvider->getFailureReceiver();
    }
}
