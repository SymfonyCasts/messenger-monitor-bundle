<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use Symfony\Component\Messenger\Envelope;

/**
 * todo handle retries.
 *
 * @internal
 */
final class StoredMessage
{
    private $id;
    private $messageClass;
    private $dispatchedAt;
    private $receivedAt;
    private $handledAt;

    public function __construct(string $id, string $messageClass, \DateTimeImmutable $dispatchedAt, ?\DateTimeImmutable $receivedAt = null, ?\DateTimeImmutable $handledAt = null)
    {
        $this->id = $id;
        $this->messageClass = $messageClass;
        $this->dispatchedAt = $dispatchedAt;

        if (null !== $receivedAt) {
            $this->receivedAt = $receivedAt;

            if (null !== $handledAt) {
                $this->handledAt = $handledAt;
            }
        } elseif (null !== $handledAt) {
            throw new \RuntimeException('"receivedAt" could not be null if "handledAt" is not null');
        }
    }

    public static function fromEnvelope(Envelope $envelope): self
    {
        /** @var MonitorIdStamp $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            throw new \RuntimeException('Envelope should have a MonitorIdStamp!');
        }

        return new self(
            $monitorIdStamp->getId(),
            \get_class($envelope->getMessage()),
            \DateTimeImmutable::createFromFormat('U', (string) time())
        );
    }

    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            $row['id'],
            $row['class'],
            new \DateTimeImmutable($row['dispatched_at']),
            null !== $row['received_at'] ? new \DateTimeImmutable($row['received_at']) : null,
            null !== $row['handled_at'] ? new \DateTimeImmutable($row['handled_at']) : null
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getDispatchedAt(): \DateTimeImmutable
    {
        return $this->dispatchedAt;
    }

    public function setReceivedAt(\DateTimeImmutable $receivedAt): void
    {
        $this->receivedAt = $receivedAt;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setHandledAt(\DateTimeImmutable $handledAt): void
    {
        $this->handledAt = $handledAt;
    }

    public function getHandledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }
}
