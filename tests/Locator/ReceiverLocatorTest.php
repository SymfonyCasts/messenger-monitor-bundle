<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Locator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;
use SymfonyCasts\MessengerMonitorBundle\Exception\ReceiverDoesNotExistException;
use SymfonyCasts\MessengerMonitorBundle\Locator\ReceiverLocator;

final class ReceiverLocatorTest extends TestCase
{
    public function testGetReceiver(): void
    {
        $messengerReceiverLocator = $this->createMock(ServiceProviderInterface::class);
        $receiverLocator = new ReceiverLocator($messengerReceiverLocator, ['foo', 'bar']);

        $messengerReceiverLocator->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $messengerReceiverLocator->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($receiver = $this->createMock(ReceiverInterface::class));

        $this->assertSame($receiver, $receiverLocator->getReceiver('foo'));
    }

    public function testExceptionOnUnknownReceiverName(): void
    {
        $this->expectException(ReceiverDoesNotExistException::class);

        $messengerReceiverLocator = $this->createMock(ServiceProviderInterface::class);
        $receiverLocator = new ReceiverLocator($messengerReceiverLocator, ['foo', 'bar']);

        $receiverLocator->getReceiver('baz');
    }

    public function testExceptionOnUnknownReceiverNameInMessengerLocator(): void
    {
        $this->expectException(ReceiverDoesNotExistException::class);

        $messengerReceiverLocator = $this->createMock(ServiceProviderInterface::class);
        $receiverLocator = new ReceiverLocator($messengerReceiverLocator, ['foo', 'bar']);

        $messengerReceiverLocator->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(false);

        $receiverLocator->getReceiver('foo');
    }

    public function testReceiversMapping(): void
    {
        $messengerReceiverLocator = $this->createMock(ServiceProviderInterface::class);
        $receiverLocator = new ReceiverLocator($messengerReceiverLocator, ['foo', 'bar']);

        $messengerReceiverLocator->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls(true, true);

        $messengerReceiverLocator->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls(
                $receiverFoo = $this->createMock(ReceiverInterface::class),
                $receiverBar = $this->createMock(ReceiverInterface::class)
            );

        $this->assertSame(
            ['foo' => $receiverFoo, 'bar' => $receiverBar],
            $receiverLocator->getReceiversMapping()
        );
    }
}
