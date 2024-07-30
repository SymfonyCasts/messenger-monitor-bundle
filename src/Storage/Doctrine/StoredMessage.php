<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine;

use Symfony\Component\Messenger\Envelope;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Exception\MessengerIdStampMissingException;

/**
 * @internal
 */
final class StoredMessage
{
    private ?\DateTimeImmutable $receivedAt = null;
    private ?\DateTimeImmutable $handledAt = null;
    private ?\DateTimeImmutable $failedAt = null;

    public function __construct(private string $messageUid, private string $messageClass, private \DateTimeImmutable $dispatchedAt, private ?int $id = null, ?\DateTimeImmutable $receivedAt = null, ?\DateTimeImmutable $handledAt = null, ?\DateTimeImmutable $failedAt = null, private ?string $receiverName = null)
    {
        if (null !== $receivedAt) {
            $this->receivedAt = $receivedAt;
            $this->handledAt = $handledAt;
            $this->failedAt = $failedAt;
        } elseif (null !== $handledAt || null !== $failedAt) {
            throw new \RuntimeException('"receivedAt" could not be null if "handledAt" or "failedAt" is not null');
        }
    }

    public static function fromEnvelope(Envelope $envelope): self
    {
        /** @var MonitorIdStamp|null $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            throw new MessengerIdStampMissingException();
        }

        return new self(
            $monitorIdStamp->getId(),
            $envelope->getMessage()::class,
            \DateTimeImmutable::createFromFormat('U', (string) time()),
            null
        );
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @psalm-ignore-nullable-return
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageUid(): string
    {
        return $this->messageUid;
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getDispatchedAt(): \DateTimeImmutable
    {
        return $this->dispatchedAt;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeImmutable $receivedAt): void
    {
        $this->receivedAt = $receivedAt;
    }

    public function getHandledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }

    public function setHandledAt(\DateTimeImmutable $handledAt): void
    {
        $this->handledAt = $handledAt;
    }

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failedAt;
    }

    public function setFailedAt(\DateTimeImmutable $failedAt): void
    {
        $this->failedAt = $failedAt;
    }

    public function setReceiverName(string $receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
    }
}
