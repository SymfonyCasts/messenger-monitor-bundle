<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\SymfonyCastsMessengerMonitorExtension;

final class SymfonyCastsMessengerMonitorExtensionTest extends TestCase
{
    public function testLoadWithoutConfiguration(): void
    {
        (new SymfonyCastsMessengerMonitorExtension())->load([], $container = new ContainerBuilder());

        $doctrineConnectionFactoryDefinition = $container->getDefinition('symfonycasts.messenger_monitor.storage.doctrine_connection_factory');
        $this->assertSame('default', $doctrineConnectionFactoryDefinition->getArgument(1));
        $this->assertSame('messenger_monitor', $doctrineConnectionFactoryDefinition->getArgument(2));

        $this->assertSame(
            'symfonycasts.messenger_monitor.statistics.doctrine_processor',
            (string) $container->getAlias('symfonycasts.messenger_monitor.statistics.processor')
        );
    }

    public function testLoadWithoutCustomDoctrineConfiguration(): void
    {
        (new SymfonyCastsMessengerMonitorExtension())->load(
            [
                [
                    'doctrine' => [
                        'connection' => 'custom_connection',
                        'table_name' => 'custom_table_name',
                    ]
                ]
            ],
            $container = new ContainerBuilder()
        );

        $doctrineConnectionFactoryDefinition = $container->getDefinition('symfonycasts.messenger_monitor.storage.doctrine_connection_factory');
        $this->assertSame('custom_connection', $doctrineConnectionFactoryDefinition->getArgument(1));
        $this->assertSame('custom_table_name', $doctrineConnectionFactoryDefinition->getArgument(2));
    }

    public function testLoadWithRedisConfiguration(): void
    {
        (new SymfonyCastsMessengerMonitorExtension())->load(
            [
                [
                    'driver' => 'redis'
                ]
            ],
            $container = new ContainerBuilder()
        );

        $this->assertFalse($container->hasDefinition('symfonycasts.messenger_monitor.storage.doctrine_connection'));
    }
}
