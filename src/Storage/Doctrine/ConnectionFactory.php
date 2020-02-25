<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage\Doctrine;

use Doctrine\Persistence\ConnectionRegistry;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\Driver\MySQLDriver;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @internal
 */
final class ConnectionFactory
{
    private $registry;
    private $connectionName;
    private $tableName;

    public function __construct(ConnectionRegistry $registry, string $connectionName, string $tableName)
    {
        $this->registry = $registry;
        $this->connectionName = $connectionName;
        $this->tableName = $tableName;
    }

    public function __invoke(): Connection
    {
        try {
            return new Connection(
                $this->registry->getConnection($this->connectionName),
                new MySQLDriver(),
                $this->tableName
            );
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidConfigurationException(sprintf('Doctrine connection with name "%s" does not exist', $this->connectionName));
        }
    }
}
