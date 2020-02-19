<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle;

use KaroIO\MessengerMonitorBundle\DependencyInjection\FailureReceiverPass;
use KaroIO\MessengerMonitorBundle\DependencyInjection\ReceiverLocatorPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @internal
 */
final class KaroIOMessengerMonitorBundle extends Bundle
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
        $container->addCompilerPass(new FailureReceiverPass());
    }
}
