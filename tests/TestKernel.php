<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use KaroIO\MessengerMonitorBundle\KaroIOMessengerMonitorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

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
            new KaroIOMessengerMonitorBundle(),
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

    protected function build(ContainerBuilder $container)
    {
        // set all services public in order to access them
        // with static::$container->get('service') in tests
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(
            function (ContainerBuilder $container) {
                $container->setParameter('kernel.secret', 123);
                $container->prependExtensionConfig(
                    'framework',
                    [
                        'session' => ['enabled' => true],
                        'router' => [
                            'resource' => 'kernel::loadRoutes',
                            'enabled' => true,
                        ]
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
                $container->loadFromExtension('karo_io_messenger_monitor', $this->bundleOptions);
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
