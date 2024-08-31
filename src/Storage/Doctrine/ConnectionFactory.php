<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver\MySQLDriver;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver\PostgreSQLDriver;

/**
 * @internal
 */
final class ConnectionFactory
{
    public function __construct(private ConnectionRegistry $registry, private string $connectionName, private string $tableName)
    {
    }

    public function __invoke(): Connection
    {
        try {
            /** @var DBALConnection $driverConnection */
            $driverConnection = $this->registry->getConnection($this->connectionName);
            $databasePlatform = $driverConnection->getDatabasePlatform()->getName();

            $driver = match ($databasePlatform) {
                'mysql' => new MySQLDriver(),
                'postgresql' => new PostgreSQLDriver(),
                default => throw new InvalidConfigurationException(\sprintf('Doctrine platform "%s" is not supported', $databasePlatform)),
            };

            return new Connection($driverConnection, $driver, $this->tableName);
        } catch (\InvalidArgumentException) {
            throw new InvalidConfigurationException(\sprintf('Doctrine connection with name "%s" does not exist', $this->connectionName));
        }
    }
}
