<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\ReceiverLocatorPass;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;

final class ReceiverLocatorPassTest extends TestCase
{
    public function testProcessReceiverLocators(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition(FailureReceiverName::class, ['receiverLocator', null]);
        $container->setDefinition('symfonycasts.messenger_monitor.receiver_locator', $definition);

        $definition = new Definition(
            ConsumeMessagesCommand::class,
            [
                'routableBus',
                'receiverLocator',
                'eventDispatcher',
                'logger',
                $receiverNames = ['receiverName1', 'receiverName2'],
            ]
        );
        $container->setDefinition('console.command.messenger_consume_messages', $definition);

        $compilerPass = new ReceiverLocatorPass();
        $compilerPass->process($container);
        $receiverLocatorDefinition = $container->getDefinition('symfonycasts.messenger_monitor.receiver_locator');

        $this->assertSame($receiverNames, $receiverLocatorDefinition->getArgument(1));
    }
}
