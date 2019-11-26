<?php


namespace KaroIO\MessengerMonitorBundle;

use KaroIO\MessengerMonitorBundle\DependencyInjection\ReceiverLocatorPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KaroIOMessengerMonitorBundleBundle extends Bundle
{
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
