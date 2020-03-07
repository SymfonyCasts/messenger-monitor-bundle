<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage\Doctrine;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;
use Doctrine\DBAL\Types\Types;
use KaroIO\MessengerMonitorBundle\Statistics\MetricsPerMessageType;
use KaroIO\MessengerMonitorBundle\Statistics\Statistics;
use KaroIO\MessengerMonitorBundle\Storage\Doctrine\Driver\SQLDriver;

/**
 * @internal
 * @final
 */
class Connection
{
    private $driverConnection;
    private $SQLDriver;
    private $tableName;
    private $schemaSynchronizer;

    public function __construct(DBALConnection $driverConnection, SQLDriver $SQLDriver, string $tableName)
    {
        $this->driverConnection = $driverConnection;
        $this->SQLDriver = $SQLDriver;
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
                ->set('receiver_name', ':receiver_name')
                ->set('handled_at', ':handled_at')
                ->where('id = :id')
                ->getSQL(),
            [
                'received_at' => $storedMessage->getReceivedAt(),
                'handled_at' => $storedMessage->getHandledAt(),
                'receiver_name' => $storedMessage->getReceiverName(),
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
            ['id' => $id]
        );

        if (false === $row = $statement->fetch()) {
            return null;
        }

        return new StoredMessage(
            $row['id'],
            $row['class'],
            new \DateTimeImmutable($row['dispatched_at']),
            null !== $row['received_at'] ? new \DateTimeImmutable($row['received_at']) : null,
            null !== $row['handled_at'] ? new \DateTimeImmutable($row['handled_at']) : null,
            $row['receiver_name'] ?? null
        );
    }

    public function getStatistics(\DateTimeImmutable $from, \DateTimeImmutable $to): Statistics
    {
        $statement = $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->select('count(id) as countMessagesOnPeriod, class')
                ->addSelect(sprintf('AVG(%s) AS averageWaitingTime', $this->SQLDriver->getDateDiffInSecondsExpression('received_at', 'dispatched_at')))
                ->addSelect(sprintf('AVG(%s) AS averageHandlingTime', $this->SQLDriver->getDateDiffInSecondsExpression('handled_at', 'received_at')))
                ->from($this->tableName)
                ->where('handled_at >= :from')
                ->andWhere('handled_at <= :to')
                ->groupBy('class')
                ->getSQL(),
            ['from' => $from, 'to' => $to],
            ['from' => Types::DATETIME_IMMUTABLE, 'to' => Types::DATETIME_IMMUTABLE,]
        );

        $statistics = new Statistics($from, $to);
        while (false !== ($row = $statement->fetch(FetchMode::ASSOCIATIVE))) {
            $statistics->add(
                new MetricsPerMessageType(
                    $from,
                    $to,
                    $row['class'],
                    (int) $row['countMessagesOnPeriod'],
                    (float) $row['averageWaitingTime'],
                    (float) $row['averageHandlingTime']
                )
            );
        }

        return $statistics;
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
        $table->addColumn('receiver_name', Types::STRING)->setLength(255)->setNotnull(false);
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
