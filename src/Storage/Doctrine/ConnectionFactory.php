<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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

            return new Connection($driverConnection, $this->tableName);
        } catch (\InvalidArgumentException) {
            throw new InvalidConfigurationException(sprintf('Doctrine connection with name "%s" does not exist', $this->connectionName));
        }
    }
}
