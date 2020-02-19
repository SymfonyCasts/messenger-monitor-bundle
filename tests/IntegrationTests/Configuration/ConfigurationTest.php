<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\IntegrationTests\Configuration;

use KaroIO\MessengerMonitorBundle\Tests\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class ConfigurationTest extends TestCase
{
    public function testUseTableNameWithRedisDriverThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"table_name" and "doctrine_connection" can only be used with doctrine driver.');

        $kernel = new TestKernel(
            [
                'driver' => 'redis',
                'table_name' => 'foo'
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
                'doctrine_connection' => 'foo'
            ]
        );
        $kernel->boot();
        $kernel->getContainer()->get('karo-io.messenger_monitor.storage.doctrine_connection');
    }
}
