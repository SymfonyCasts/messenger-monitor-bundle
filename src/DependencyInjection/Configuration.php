<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('symfonycasts_messenger_monitor');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode /** @phpstan-ignore-line method.notFound (The last end() call) */
            ->children()
                ->enumNode('driver')
                    ->defaultValue('doctrine')
                    ->values(['doctrine'/* , 'redis' */])
                    ->validate()
                        ->ifTrue(static fn (string $value): bool => 'doctrine' === $value && !class_exists(DBALConnection::class))
                        ->thenInvalid('Package doctrine/dbal and doctrine/doctrine-bundle are required to use doctrine driver.')
                    ->end()
                    ->validate()
                        ->ifTrue(static fn (string $value): bool => 'redis' === $value && !class_exists(\Redis::class))
                        ->thenInvalid('Extension php-redis is required to use redis driver.')
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->children()
                        ->scalarNode('table_name')->defaultNull()->end()
                        ->scalarNode('connection')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(static fn (array $value): bool => (isset($value['doctrine']['table_name']) || isset($value['doctrine']['connection'])) && 'redis' === $value['driver'])
                ->thenInvalid('"doctrine.table_name" and "doctrine.connection" can only be used with doctrine driver.')
            ->end();

        return $treeBuilder;
    }
}
