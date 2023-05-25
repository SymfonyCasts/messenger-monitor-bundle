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

/**
 * @internal
 *
 * @final
 */
class Connection
{
    public function __construct(private DBALConnection $driverConnection, private string $tableName)
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
                'dispatched_at' => (float) $storedMessage->getDispatchedAt()->format('U.u'),
            ]
        );

        $storedMessage->setId((int) $this->driverConnection->lastInsertId());
    }

    public function updateMessage(StoredMessage $storedMessage): void
    {
        $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->update($this->tableName)
                ->set('waiting_time', ':waiting_time')
                ->set('receiver_name', ':receiver_name')
                ->set('handling_time', ':handling_time')
                ->set('failing_time', ':failing_time')
                ->where('id = :id')
                ->getSQL(),
            [
                'waiting_time' => $storedMessage->getWaitingTime(),
                'receiver_name' => $storedMessage->getReceiverName(),
                'handling_time' => $storedMessage->getHandlingTime(),
                'failing_time' => $storedMessage->getFailingTime(),
                'id' => $storedMessage->getId(),
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

        /** @psalm-suppress PossiblyFalseArgument */
        return new StoredMessage(
            $row['message_uid'],
            $row['class'],
            \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6f', $row['dispatched_at'])),
            (int) $row['id'],
            null !== $row['waiting_time'] ? (float) $row['waiting_time'] : null,
            $row['receiver_name'] ?? null,
            null !== $row['handling_time'] ? (float) $row['handling_time'] : null,
            null !== $row['failing_time'] ? (float) $row['failing_time'] : null
        );
    }

    public function getStatistics(\DateTimeImmutable $fromDate, \DateTimeImmutable $toDate): Statistics
    {
        $statement = $this->executeQuery(
            $this->driverConnection->createQueryBuilder()
                ->select('count(id) as count_messages_on_period, class')
                ->addSelect('AVG(waiting_time) AS average_waiting_time')
                ->addSelect('AVG(handling_time) AS average_handling_time')
                ->from($this->tableName)
                ->where('dispatched_at >= :from_date')
                ->andWhere('dispatched_at <= :to_date')
                ->groupBy('class')
                ->getSQL(),
            [
                'from_date' => (float) $fromDate->format('U'),
                'to_date' => (float) $toDate->format('U'),
            ]
        );

        $statistics = new Statistics($fromDate, $toDate);
        while (false !== ($row = $statement->fetchAssociative())) {
            $statistics->add(
                new MetricsPerMessageType(
                    $fromDate,
                    $toDate,
                    $row['class'],
                    (int) $row['count_messages_on_period'],
                    $row['average_waiting_time'] ? (float) $row['average_waiting_time'] : null,
                    $row['average_handling_time'] ? (float) $row['average_handling_time'] : null
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
        $table->addColumn('dispatched_at', Types::DECIMAL, ['precision' => 16, 'scale' => 6])->setNotnull(true);
        $table->addColumn('waiting_time', Types::DECIMAL, ['precision' => 16, 'scale' => 6])->setNotnull(false);
        $table->addColumn('handling_time', Types::DECIMAL, ['precision' => 16, 'scale' => 6])->setNotnull(false);
        $table->addColumn('failing_time', Types::DECIMAL, ['precision' => 16, 'scale' => 6])->setNotnull(false);
        $table->addColumn('receiver_name', Types::STRING)->setLength(255)->setNotnull(false);
        $table->addIndex(['dispatched_at']);
        $table->addIndex(['class']);
        $table->addIndex(['message_uid']);
        $table->setPrimaryKey(['id']);
    }
}
