<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\IntegrationTests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
            $this->markTestSkipped(\sprintf('Can\'t connect to connection: %s', $exception->getMessage()));
        }

        $this->doctrineConnection = self::getContainer()->get('test.symfonycasts.messenger_monitor.storage.doctrine_connection');

        $databasePlatform = $connection->getDatabasePlatform()->getName();

        $truncateTable = match ($databasePlatform) {
            'mysql' => 'TRUNCATE TABLE messenger_monitor',
            'postgresql' => 'TRUNCATE TABLE messenger_monitor RESTART IDENTITY',
            default => throw new InvalidConfigurationException(\sprintf('Doctrine platform "%s" is not supported', $databasePlatform)),
        };

        try {
            $connection->executeQuery($truncateTable);
        } catch (\Throwable) {
            $this->doctrineConnection->executeSchema(new Schema(), $connection);
        }
    }
}
