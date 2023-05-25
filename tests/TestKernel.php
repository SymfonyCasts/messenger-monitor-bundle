<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Security\Core\User\InMemoryUser;
use SymfonyCasts\MessengerMonitorBundle\SymfonyCastsMessengerMonitorBundle;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\FailureMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\RetryableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessageHandler;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private $bundleOptions;

    public function __construct(array $bundleOptions = [])
    {
        parent::__construct('test', true);

        $this->bundleOptions = $bundleOptions;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SymfonyCastsMessengerMonitorBundle(),
            new TwigBundle(),
            new SecurityBundle(),
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

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import(__DIR__.'/../src/Resources/config/routing.xml');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container)
    {
        $container->setAlias(
            'test.symfonycasts.messenger_monitor.storage.doctrine_connection',
            'symfonycasts.messenger_monitor.storage.doctrine_connection'
        )->setPublic(true);

        $container->register('logger', NullLogger::class);

        $container->setAlias('test.messenger.bus.default', 'messenger.bus.default')->setPublic(true);
        $container->setAlias('test.messenger.receiver_locator', 'messenger.receiver_locator')->setPublic(true);

        $messageHandlerDefinition = new Definition(TestableMessageHandler::class);
        $messageHandlerDefinition->addTag('messenger.message_handler');
        $container->setDefinition('test.message_handler', $messageHandlerDefinition);

        $container->setParameter('kernel.secret', 123);

        $container->loadFromExtension(
            'framework',
            [
                'router' => [
                    'utf8' => true,
                ],
                'session' => [
                    'enabled' => true,
                    'storage_factory_id' => 'session.storage.factory.mock_file',
                ],
                'messenger' => [
                    'reset_on_message' => true,
                    'failure_transport' => 'failed',
                    'transports' => [
                        'queue' => [
                            'dsn' => 'doctrine://default?queue_name=queue',
                            'retry_strategy' => ['max_retries' => 0],
                        ],
                        'queue_with_retry' => [
                            'dsn' => 'doctrine://default?queue_name=queue_with_retry',
                            'retry_strategy' => ['max_retries' => 1, 'delay' => 0, 'multiplier' => 1],
                        ],
                        'failed' => 'doctrine://default?queue_name=failed',
                    ],
                    'routing' => [
                        TestableMessage::class => 'queue',
                        FailureMessage::class => 'queue',
                        RetryableMessage::class => 'queue_with_retry',
                    ],
                ],
                'test' => true,
            ]
        );

        $container->loadFromExtension(
            'doctrine',
            [
                'dbal' => [
                    'connections' => [
                        'default' => [
                            'url' => '%env(resolve:TEST_DATABASE_DSN)%',
                            'logging' => false,
                        ],
                    ],
                ],
            ]
        );

        $container->loadFromExtension(
            'security',
            [
                'enable_authenticator_manager' => true,
                'providers' => [
                    'in_memory' => [
                        'memory' => [
                            'users' => [
                                'admin' => ['password' => 'password', 'roles' => ['ROLE_MESSENGER_ADMIN']],
                                'user' => ['password' => 'password', 'roles' => ['ROLE_USER']],
                            ],
                        ],
                    ],
                ],
                'password_hashers' => [InMemoryUser::class => 'plaintext'],
                'firewalls' => [
                    'main' => [
                        'provider' => 'in_memory',
                        'http_basic' => true,
                    ],
                ],
            ]
        );

        $container->loadFromExtension('symfonycasts_messenger_monitor', $this->bundleOptions);
    }
}
