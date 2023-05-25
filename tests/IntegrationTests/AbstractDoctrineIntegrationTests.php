<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection as DoctrineConnection;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestKernel;

abstract class AbstractDoctrineIntegrationTests extends KernelTestCase
{
    protected DoctrineConnection $doctrineConnection;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel();
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');

        try {
            $connection->connect();
        } catch (\Exception $exception) {
            $this->markTestSkipped(sprintf('Can\'t connect to connection: %s', $exception->getMessage()));
        }

        $connection->executeQuery('DROP TABLE IF EXISTS messenger_monitor');

        $this->doctrineConnection = self::getContainer()->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection');
        $this->doctrineConnection->executeSchema(new Schema(), $connection);
    }
}
