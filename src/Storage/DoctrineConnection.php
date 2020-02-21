<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;
use Doctrine\DBAL\Types\Types;

/**
 * @internal
 * @final
 */
class DoctrineConnection
{
    private $driverConnection;
    private $schemaSynchronizer;
    private $tableName;

    public function __construct(Connection $driverConnection, string $tableName)
    {
        $this->driverConnection = $driverConnection;
        $this->tableName = $tableName;
    }

    public function saveMessage(StoredMessage $storedMessage): void
    {
        $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->insert($this->tableName)
                ->values(
                    [
                        'id' => ':id',
                        'class' => ':class',
                        'dispatched_at' => ':dispatched_at',
                    ]
                )
                ->getSQL(),
            [
                'id' => $storedMessage->getId(),
                'class' => $storedMessage->getMessageClass(),
                'dispatched_at' => $storedMessage->getDispatchedAt(),
            ],
            [
                'dispatched_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    public function updateMessage(StoredMessage $storedMessage): void
    {
        $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->update($this->tableName)
                ->set('received_at', ':received_at')
                ->set('handled_at', ':handled_at')
                ->where('id = :id')
                ->getSQL(),
            [
                'received_at' => $storedMessage->getReceivedAt(),
                'handled_at' => $storedMessage->getHandledAt(),
                'id' => $storedMessage->getId()
            ],
            [
                'received_at' => Types::DATETIME_IMMUTABLE,
                'handled_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    public function findMessage(string $id): ?StoredMessage
    {
        $statement = $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->select('*')
                ->from($this->tableName)
                ->where('id = :id')
                ->getSQL(),
            [
                'id' => $id
            ]
        );

        if (false === $row = $statement->fetch()) {
            return null;
        }

        return StoredMessage::fromDatabaseRow($row);
    }

    private function executeQuery(string $sql, array $parameters = [], array $types = []): ResultStatement
    {
        try {
            $stmt = $this->driverConnection->executeQuery($sql, $parameters, $types);
        } catch (TableNotFoundException $e) {
            if ($this->driverConnection->isTransactionActive()) {
                throw $e;
            }

            $this->setup();

            $stmt = $this->driverConnection->executeQuery($sql, $parameters, $types);
        }

        return $stmt;
    }

    private function setup(): void
    {
        $this->getSchemaSynchronizer()->updateSchema($this->getSchema(), true);
    }

    private function getSchema(): Schema
    {
        $schema = new Schema([], [], $this->driverConnection->getSchemaManager()->createSchemaConfig());
        $table = $schema->createTable($this->tableName);
        $table->addColumn('id', Types::GUID)->setNotnull(true);
        $table->addColumn('class', Types::STRING)->setLength(255)->setNotnull(true);
        $table->addColumn('dispatched_at', Types::DATETIME_IMMUTABLE)->setNotnull(true);
        $table->addColumn('received_at', Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn('handled_at', Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn('retries', Types::INTEGER)->setDefault(0);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['dispatched_at']);
        $table->addIndex(['class']);

        return $schema;
    }

    private function getSchemaSynchronizer(): SingleDatabaseSynchronizer
    {
        if (null === $this->schemaSynchronizer) {
            $this->schemaSynchronizer = new SingleDatabaseSynchronizer($this->driverConnection);
        }

        return $this->schemaSynchronizer;
    }
}
