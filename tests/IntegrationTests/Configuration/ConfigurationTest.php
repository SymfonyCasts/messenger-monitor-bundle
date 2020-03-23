<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests\Configuration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestKernel;

final class ConfigurationTest extends TestCase
{
    /**
     * @requires extension redis
     */
    public function testUseTableNameWithRedisDriverThrowsException(): void
    {
        $this->markTestSkipped('Redis not available yet.');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"doctrine.table_name" and "doctrine.connection" can only be used with doctrine driver.');

        $kernel = TestKernel::withBundleOptions(
            [
                'driver' => 'redis',
                'doctrine' => [
                    'table_name' => 'foo',
                ],
            ]
        );
        $kernel->boot();
    }

    public function testChoseWrongConnectionNameThrowException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Doctrine connection with name "foo" does not exist');

        $kernel = TestKernel::withBundleOptions(
            [
                'driver' => 'doctrine',
                'doctrine' => [
                    'connection' => 'foo',
                ],
            ]
        );
        $kernel->boot();
        $kernel->getContainer()->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection');
    }
}
