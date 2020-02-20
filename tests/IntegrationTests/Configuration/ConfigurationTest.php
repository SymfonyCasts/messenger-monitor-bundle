<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\IntegrationTests\Configuration;

use KaroIO\MessengerMonitorBundle\Tests\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class ConfigurationTest extends TestCase
{
    public function testUseTableNameWithRedisDriverThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"doctrine.table_name" and "doctrine.connection" can only be used with doctrine driver.');

        $kernel = new TestKernel(
            [
                'driver' => 'redis',
                'doctrine' => [
                    'table_name' => 'foo',
                ]
            ]
        );
        $kernel->boot();
    }

    public function testChoseWrongConnectionNameThrowException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Doctrine connection with name "foo" does not exist');

        $kernel = new TestKernel(
            [
                'driver' => 'doctrine',
                'doctrine' => [
                    'connection' => 'foo',
                ]
            ]
        );
        $kernel->boot();
        $kernel->getContainer()->get('test.karo-io.messenger_monitor.storage.doctrine_connection');
    }
}
