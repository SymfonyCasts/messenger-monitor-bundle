<?php


namespace KaroIO\MessengerMonitor;

use KaroIO\MessengerMonitor\DependencyInjection\ReceiverLocatorPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class KaroIOMessengerMonitorBundle extends Bundle
{
    public function load() {
        die('GIPS?');

    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/Resources/config')
        );
        $loader->load('services.xml');

        $container->addCompilerPass(new ReceiverLocatorPass());
    }
}
