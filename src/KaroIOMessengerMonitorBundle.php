<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle;

use KaroIO\MessengerMonitorBundle\DependencyInjection\FailureReceiverPass;
use KaroIO\MessengerMonitorBundle\DependencyInjection\ReceiverLocatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @internal
 */
final class KaroIOMessengerMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReceiverLocatorPass());
        $container->addCompilerPass(new FailureReceiverPass());
    }
}
