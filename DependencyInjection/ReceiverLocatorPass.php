<?php

namespace KaroIO\MessengerMonitor\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReceiverLocatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('messenger-monitor.receiver-locator')) {
            if ($container->hasDefinition('console.command.messenger_consume_messages')) {

                // steal configurations already done by the MessengerPass so we dont have to duplicate the work
                // as approved by @ryanweaver with the "I've seen Nicolas do worse" certificate
                $receiverLocatorDefinition = $container->getDefinition('karo-io.messenger_monitor.receiver_locator');

                $consumeCommandDefinition = $container->getDefinition('console.command.messenger_consume_messages');
                $names = $consumeCommandDefinition->getArgument(4);
                $receiverLocatorDefinition->addArgument($names);
            }
        }
    }
}

