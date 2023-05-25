<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class ReceiverLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('symfonycasts.messenger_monitor.receiver_locator')
            && $container->hasDefinition('console.command.messenger_consume_messages')) {
            // steal configurations already done by the MessengerPass so we dont have to duplicate the work
            // as approved by @ryanweaver with the "I've seen Nicolas do worse" certificate
            $receiverLocatorDefinition = $container->getDefinition('symfonycasts.messenger_monitor.receiver_locator');

            $consumeCommandDefinition = $container->getDefinition('console.command.messenger_consume_messages');
            $names = $consumeCommandDefinition->getArgument(4);
            $receiverLocatorDefinition->replaceArgument(1, $names);
        }
    }
}
