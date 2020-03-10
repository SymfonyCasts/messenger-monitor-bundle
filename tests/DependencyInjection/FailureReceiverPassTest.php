<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Messenger\Command\FailedMessagesShowCommand;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\FailureReceiverPass;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;

final class FailureReceiverPassTest extends TestCase
{
    public function testProcessFailureReceiverDoesNotExist(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition(FailureReceiverName::class, [null]);
        $container->setDefinition('symfonycasts.messenger_monitor.failed_receiver.name', $definition);

        $compilerPass = new FailureReceiverPass();
        $compilerPass->process($container);
        $failureReceiverDefinition = $container->getDefinition('symfonycasts.messenger_monitor.failed_receiver.name');

        $this->assertNull($failureReceiverDefinition->getArgument(0));
    }

    public function testProcessFailureReceiverExists(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition(FailureReceiverName::class, [null]);
        $container->setDefinition('symfonycasts.messenger_monitor.failed_receiver.name', $definition);

        $definition = new Definition(FailedMessagesShowCommand::class, ['failureReceiverName']);
        $container->setDefinition('console.command.messenger_failed_messages_show', $definition);

        $compilerPass = new FailureReceiverPass();
        $compilerPass->process($container);
        $failureReceiverDefinition = $container->getDefinition('symfonycasts.messenger_monitor.failed_receiver.name');

        $this->assertSame('failureReceiverName', $failureReceiverDefinition->getArgument(0));
    }
}
