<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Messenger\Command\FailedMessagesShowCommand;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\FailureReceiverPass;

final class FailureReceiverPassTest extends TestCase
{
    public function testProcessFailureReceiverDoesNotExist(): void
    {
        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../src/Resources/config'));
        $loader->load('services.xml');

        $compilerPass = new FailureReceiverPass();
        $compilerPass->process($container);

        // controllers
        $this->assertTrue($container->hasDefinition('symfonycasts.messenger_monitor.controller.no_failure_transport'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.controller.retry_failed_message'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.controller.reject_failed_message'));
        $this->assertTrue($container->hasAlias('symfonycasts.messenger_monitor.controller.retry_failed_message'));
        $this->assertTrue($container->hasAlias('symfonycasts.messenger_monitor.controller.reject_failed_message'));

        // failure services
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.controller.reject_failed_message'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.failed_message.reject'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.failed_message.retry'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.failed_message.repository'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.failed_receiver.provider'));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.failed_receiver.name'));
    }

    public function testProcessFailureReceiverExists(): void
    {
        $container = new ContainerBuilder();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../src/Resources/config'));
        $loader->load('services.xml');

        $definition = new Definition(FailedMessagesShowCommand::class, ['failureReceiverName']);
        $container->setDefinition('console.command.messenger_failed_messages_show', $definition);

        $compilerPass = new FailureReceiverPass();
        $compilerPass->process($container);
        $failureReceiverDefinition = $container->getDefinition('symfonycasts.messenger_monitor.failed_receiver.name');

        $this->assertSame('failureReceiverName', $failureReceiverDefinition->getArgument(0));
        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.controller.no_failure_transport'));
    }
}
