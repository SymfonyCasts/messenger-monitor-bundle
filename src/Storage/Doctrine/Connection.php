<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use SymfonyCasts\MessengerMonitorBundle\Statistics\MetricsPerMessageType;
use SymfonyCasts\MessengerMonitorBundle\Statistics\Statistics;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver\SQLDriverInterface;

/**
 * @internal
 *
 * @final
 */
class Connection
{
    public function __construct(private DBALConnection $driverConnection, private SQLDriverInterface $SQLDriver, private string $tableName)
    {
    }

    public function saveMessage(StoredMessage $storedMessage): void
    {
        $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->insert($this->tableName)
                ->values(
                    [
                        'message_uid' => ':message_uid',
                        'class' => ':class',
                        'dispatched_at' => ':dispatched_at',
                    ]
                )
                ->getSQL(),
            [
                'message_uid' => $storedMessage->getMessageUid(),
                'class' => $storedMessage->getMessageClass(),
                'dispatched_at' => $storedMessage->getDispatchedAt(),
            ],
            [
                'dispatched_at' => Types::DATETIME_IMMUTABLE,
            ]
        );

        $storedMessage->setId((int) $this->driverConnection->lastInsertId());
    }

    public function updateMessage(StoredMessage $storedMessage): void
    {
        $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->update($this->tableName)
                ->set('received_at', ':received_at')
                ->set('receiver_name', ':receiver_name')
                ->set('handled_at', ':handled_at')
                ->set('failed_at', ':failed_at')
                ->where('id = :id')
                ->getSQL(),
            [
                'received_at' => $storedMessage->getReceivedAt(),
                'handled_at' => $storedMessage->getHandledAt(),
                'failed_at' => $storedMessage->getFailedAt(),
                'receiver_name' => $storedMessage->getReceiverName(),
                'id' => $storedMessage->getId(),
            ],
            [
                'received_at' => Types::DATETIME_IMMUTABLE,
                'handled_at' => Types::DATETIME_IMMUTABLE,
                'failed_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    public function findMessage(string $messageUid): ?StoredMessage
    {
        $statement = $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->select('*')
                ->from($this->tableName)
                ->where('message_uid = :message_uid')
                ->orderBy('dispatched_at', 'desc')
                ->setMaxResults(1)
                ->getSQL(),
            ['message_uid' => $messageUid]
        );

        if (false === $row = $statement->fetchAssociative()) {
            return null;
        }

        return new StoredMessage(
            $row['message_uid'],
            $row['class'],
            new \DateTimeImmutable($row['dispatched_at']),
            (int) $row['id'],
            null !== $row['received_at'] ? new \DateTimeImmutable($row['received_at']) : null,
            null !== $row['handled_at'] ? new \DateTimeImmutable($row['handled_at']) : null,
            null !== $row['failed_at'] ? new \DateTimeImmutable($row['failed_at']) : null,
            $row['receiver_name'] ?? null
        );
    }

    public function getStatistics(\DateTimeImmutable $fromDate, \DateTimeImmutable $toDate): Statistics
    {
        $statement = $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->select('count(id) as count_messages_on_period, class')
                ->addSelect(\sprintf('AVG(%s) AS average_waiting_time', $this->SQLDriver->getDateDiffInSecondsExpression('received_at', 'dispatched_at')))
                ->addSelect(\sprintf('AVG(%s) AS average_handling_time', $this->SQLDriver->getDateDiffInSecondsExpression('handled_at', 'received_at')))
                ->from($this->tableName)
                ->where('handled_at >= :from_date')
                ->andWhere('handled_at <= :to_date')
                ->groupBy('class')
                ->getSQL(),
            ['from_date' => $fromDate, 'to_date' => $toDate],
            ['from_date' => Types::DATETIME_IMMUTABLE, 'to_date' => Types::DATETIME_IMMUTABLE]
        );

        $statistics = new Statistics($fromDate, $toDate);
        while (false !== ($row = $statement->fetchAssociative())) {
            $statistics->add(
                new MetricsPerMessageType(
                    $fromDate,
                    $toDate,
                    $row['class'],
                    (int) $row['count_messages_on_period'],
                    (float) $row['average_waiting_time'],
                    (float) $row['average_handling_time']
                )
            );
        }

        return $statistics;
    }

    public function configureSchema(Schema $schema, DBALConnection $forConnection): void
    {
        // only update the schema for this connection
        if ($forConnection !== $this->driverConnection) {
            return;
        }

        if ($schema->hasTable($this->tableName)) {
            return;
        }

        $this->addTableToSchema($schema);
    }

    public function executeSchema(Schema $schema, DBALConnection $forConnection): void
    {
        $this->configureSchema($schema, $forConnection);

        foreach ($schema->toSql($this->driverConnection->getDatabasePlatform()) as $sql) {
            $this->driverConnection->executeStatement($sql);
        }
    }

    private function executeQuery(string $sql, array $parameters = [], array $types = []): Result
    {
        try {
            $stmt = $this->driverConnection->executeQuery($sql, $parameters, $types);
        } catch (TableNotFoundException $e) {
            if ($this->driverConnection->isTransactionActive()) {
                throw $e;
            }

            throw new \RuntimeException('messenger-monitor SQL table does not exist. Maybe you should create a migration or run "doctrine:schema:update"', 0, $e);
        }

        return $stmt;
    }

    private function addTableToSchema(Schema $schema): void
    {
        $table = $schema->createTable($this->tableName);
        $table->addColumn('id', Types::INTEGER)->setNotnull(true)->setAutoincrement(true);
        $table->addColumn('message_uid', Types::GUID)->setNotnull(true);
        $table->addColumn('class', Types::STRING)->setLength(255)->setNotnull(true);
        $table->addColumn('dispatched_at', Types::DATETIME_IMMUTABLE)->setNotnull(true);
        $table->addColumn('received_at', Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn('handled_at', Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn('failed_at', Types::DATETIME_IMMUTABLE)->setNotnull(false);
        $table->addColumn('receiver_name', Types::STRING)->setLength(255)->setNotnull(false);
        $table->addIndex(['dispatched_at']);
        $table->addIndex(['class']);
        $table->addIndex(['message_uid']);
        $table->setPrimaryKey(['id']);
    }
}
