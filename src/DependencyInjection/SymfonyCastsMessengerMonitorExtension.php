<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
final class SymfonyCastsMessengerMonitorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if ('doctrine' === $config['driver']) {
            // todo: throw an error if doctrine platform is not mysql nor postgresql
            $loader->load('doctrine.xml');

            $doctrineConnectionFactoryDefinition = $container->getDefinition('symfonycasts.messenger_monitor.storage.doctrine_connection_factory');

            $doctrineConnection = $config['doctrine']['connection'] ?? 'default';
            $doctrineConnectionFactoryDefinition->replaceArgument(1, $doctrineConnection);

            $tableName = $config['doctrine']['table_name'] ?? 'messenger_monitor';
            $doctrineConnectionFactoryDefinition->replaceArgument(2, $tableName);

            $container->setAlias('symfonycasts.messenger_monitor.statistics.processor', 'symfonycasts.messenger_monitor.statistics.doctrine_processor');
        }
    }

    public function getAlias(): string
    {
        return 'symfonycasts_messenger_monitor';
    }
}
