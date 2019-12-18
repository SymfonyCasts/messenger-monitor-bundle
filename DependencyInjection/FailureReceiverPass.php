<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FailureReceiverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('console.command.messenger_failed_messages_show')) {
            $consumeCommandDefinition = $container->getDefinition('console.command.messenger_failed_messages_show');
            $container->setParameter('karo-io.messenger_monitor.failure_transport', $consumeCommandDefinition->getArgument(0));
        }
    }

}
