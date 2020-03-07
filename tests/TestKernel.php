<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

                $container->setParameter('kernel.secret', 123);
                $container->prependExtensionConfig(
                    'framework',
                    [
                        'session' => ['enabled' => true],
                        'router' => [
                            'resource' => 'kernel::loadRoutes',
                            'enabled' => true,
                        ],
                    ]
                );
                $container->prependExtensionConfig(
                    'doctrine',
                    [
                        'dbal' => [
                            'connections' => [
                                'default' => [
                                    'url' => $_ENV['TEST_DATABASE_DSN'],
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
        $routes->import(__DIR__.'/../Resources/config/routing.xml');

        return $routes->build();
    }
}
