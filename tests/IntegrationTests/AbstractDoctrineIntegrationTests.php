<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\IntegrationTests;

use Doctrine\DBAL\Connection;
use KaroIO\MessengerMonitorBundle\Tests\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractDoctrineIntegrationTests extends KernelTestCase
{
    protected static function createKernel(array $options = [])
    {
        return new TestKernel();
    }

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::$container->get('doctrine.dbal.default_connection');

        try {
            $connection->connect();
        } catch (\Exception $exception) {
            $this->markTestSkipped(sprintf('Can\'t connect to connection: %s', $exception->getMessage()));
        }

        $connection->executeQuery('DROP TABLE IF EXISTS karo_io_messenger_monitor');
    }
}
