<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\FailureReceiver;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

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

    public function testExceptionWithNoReceiverName(): void
    {
        $this->expectException(FailureReceiverDoesNotExistException::class);

        $receiverLocator = $this->createMock(ReceiverLocator::class);
        $failureReceiverProvider = new FailureReceiverProvider($receiverLocator, new FailureReceiverName(null));

        $failureReceiverProvider->getFailureReceiver();
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
