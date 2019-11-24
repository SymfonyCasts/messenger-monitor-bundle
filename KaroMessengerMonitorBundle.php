<?php


namespace Karo\MessengerMonitor;

use Karo\MessengerMonitor\DependencyInjection\ReceiverLocatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KaroMessengerMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ReceiverLocatorPass());
    }
}
