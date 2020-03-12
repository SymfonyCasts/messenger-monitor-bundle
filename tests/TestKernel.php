<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;
use SymfonyCasts\MessengerMonitorBundle\SymfonyCastsMessengerMonitorBundle;

final class TestKernel extends Kernel
{
    private $bundleOptions;

    public function __construct(array $bundleOptions = [])
    {
        parent::__construct('test', true);

        $this->bundleOptions = $bundleOptions;
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SymfonyCastsMessengerMonitorBundle(),
            new TwigBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__.'/tmp';
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/cache/'.md5(json_encode($this->bundleOptions));
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(
            function (ContainerBuilder $container) {
                $container->setAlias(
                    'test.symfonycasts.messenger_monitor.storage.doctrine_connection',
                    'symfonycasts.messenger_monitor.storage.doctrine_connection'
                )->setPublic(true);

                $container->setAlias('test.messenger.bus.default', 'messenger.bus.default')->setPublic(true);
                $container->setAlias('test.messenger.receiver_locator', 'messenger.receiver_locator')->setPublic(true);

                $messageHandlerDefinition = new Definition(TestableMessageHandler::class);
                $messageHandlerDefinition->addTag('messenger.message_handler');
                $container->setDefinition('test.message_handler', $messageHandlerDefinition);

                $container->setParameter('kernel.secret', 123);
                $container->prependExtensionConfig(
                    'framework',
                    [
                        'session' => [
                            'enabled' => true,
                            'storage_id' => 'session.storage.mock_file'
                        ],
                        'router' => [
                            'resource' => 'kernel::loadRoutes',
                            'type' => 'service',
                            'enabled' => true,
                        ],
                        'messenger' => [
                            'failure_transport' => 'failed',
                            'transports' => [
                                'queue' => [
                                    'dsn' => 'doctrine://default?queue_name=queue',
                                    'retry_strategy' => ['max_retries' => 0]
                                ],
                                'failed' => 'doctrine://default?queue_name=failed',
                            ],
                            'routing' => [
                                TestableMessage::class => 'queue'
                            ]
                        ],
                        'test' => true,
                    ]
                );

                if (!$container->hasDefinition('kernel')) {
                    $container->register('kernel', static::class)
                        ->setSynthetic(true)
                        ->setPublic(true);
                }

                $kernelDefinition = $container->getDefinition('kernel');
                $kernelDefinition->addTag('routing.route_loader');

                $container->prependExtensionConfig(
                    'doctrine',
                    [
                        'dbal' => [
                            'connections' => [
                                'default' => [
                                    'url' => getenv('TEST_DATABASE_DSN'),
                                    'logging' => false,
                                ],
                            ],
                        ],
                    ]
                );

                $container->loadFromExtension('symfonycasts_messenger_monitor', $this->bundleOptions);
            }
        );
    }

    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $routes = new RouteCollectionBuilder($loader);
        $routes->import(__DIR__.'/../src/Resources/config/routing.xml');

        return $routes->build();
    }
}
