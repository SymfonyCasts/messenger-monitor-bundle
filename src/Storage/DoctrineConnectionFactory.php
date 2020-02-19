<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage;

use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class DoctrineConnectionFactory
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

    public function __invoke(): DoctrineConnection
    {
        try {
            return new DoctrineConnection(
                $this->registry->getConnection($this->connectionName),
                $this->tableName
            );
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidConfigurationException(sprintf('Doctrine connection with name "%s" does not exist', $this->connectionName));
        }
    }
}
