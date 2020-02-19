<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class FailureReceiverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('karo-io.messenger_monitor.failed_receiver.name')) {
            return;
        }

        $failureReceiverNameDefinition = $container->getDefinition('karo-io.messenger_monitor.failed_receiver.name');

        if (!$container->hasDefinition('console.command.messenger_failed_messages_show')) {
            $failureReceiverNameDefinition->replaceArgument(0, null);

            return;
        }

        $consumeCommandDefinition = $container->getDefinition('console.command.messenger_failed_messages_show');
        $failureReceiverNameDefinition->replaceArgument(0, $consumeCommandDefinition->getArgument(0));
    }
}
