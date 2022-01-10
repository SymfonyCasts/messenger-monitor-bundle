<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 *
 * If failure_transport configured:
 *  - injects failure_transport in FailureReceiverName
 *  - disable NoFailureTransportController
 * Otherwise:
 *  - replace RetryFailedMessageController and RejectFailedMessageController by NoFailureTransportController
 *  - disable every service related to failure transport
 */
final class FailureReceiverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        // if this service does not exist, failure_transport is not configured
        if (!$container->hasDefinition('console.command.messenger_failed_messages_show')) {
            $this->switchFailureTransportControllers($container);
            $this->disableFailureTransportServices($container);

            return;
        }

        $consumeCommandDefinition = $container->getDefinition('console.command.messenger_failed_messages_show');
        $failureReceiverNameDefinition = $container->getDefinition('symfonycasts.messenger_monitor.failed_receiver.name');
        $failureReceiverNameDefinition->replaceArgument(0, $consumeCommandDefinition->getArgument(0));

        $container->removeDefinition('symfonycasts.messenger_monitor.controller.no_failure_transport');
    }

    private function switchFailureTransportControllers(ContainerBuilder $container): void
    {
        $container->removeDefinition('symfonycasts.messenger_monitor.controller.retry_failed_message');
        $container->removeDefinition('symfonycasts.messenger_monitor.controller.reject_failed_message');

        $container->setAlias(
            'symfonycasts.messenger_monitor.controller.retry_failed_message',
            'symfonycasts.messenger_monitor.controller.no_failure_transport'
        )->setPublic(true);

        $container->setAlias(
            'symfonycasts.messenger_monitor.controller.reject_failed_message',
            'symfonycasts.messenger_monitor.controller.no_failure_transport'
        )->setPublic(true);
    }

    private function disableFailureTransportServices(ContainerBuilder $container): void
    {
        $container->removeDefinition('symfonycasts.messenger_monitor.failed_message.reject');
        $container->removeDefinition('symfonycasts.messenger_monitor.failed_message.retry');
        $container->removeDefinition('symfonycasts.messenger_monitor.failed_message.repository');
        $container->removeDefinition('symfonycasts.messenger_monitor.failed_receiver.provider');
        $container->removeDefinition('symfonycasts.messenger_monitor.failed_receiver.name');
    }
}
