<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\DependencyInjection;

use Doctrine\DBAL\Connection as DBALConnection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('karo_io_messenger_monitor');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->enumNode('driver')
                    ->defaultValue('doctrine')
                    ->values(['doctrine', 'redis'])
                    ->validate()
                        ->ifTrue(function($value) {
                            return 'doctrine' === $value && !class_exists(DBALConnection::class);
                        })
                        ->thenInvalid('Package doctrine/dbal is required to use doctrine driver.')
                    ->end()
                    ->validate()
                        ->ifTrue(function($value) {
                            return 'redis' === $value && !class_exists(\Redis::class);
                        })
                        ->thenInvalid('Extension php-redis is required to use redis driver.')
                    ->end()
                ->end()
                ->scalarNode('table_name')
                    ->defaultNull()
                ->end()
                ->scalarNode('doctrine_connection')
                    ->defaultNull()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($value) {
                    return (null !== $value['table_name'] || null !== $value['doctrine_connection']) && 'redis' === $value['driver'];
                })
                ->thenInvalid('"table_name" and "doctrine_connection" can only be used with doctrine driver.')
            ->end();

        return $treeBuilder;
    }
}
