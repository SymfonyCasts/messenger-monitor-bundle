<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine;

use Symfony\Component\Messenger\Envelope;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;

/**
 * @internal
 */
final class StoredMessage
{
    private $id;
    private $messageUid;
    private $messageClass;
    private $receiverName;
    private $dispatchedAt;
    private $receivedAt;
    private $handledAt;

    public function __construct(string $id, string $messageUid, string $messageClass, \DateTimeImmutable $dispatchedAt, ?\DateTimeImmutable $receivedAt = null, ?\DateTimeImmutable $handledAt = null, ?string $receiverName = null)
    {
        $this->id = $id;
        $this->messageUid = $messageUid;
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

        $this->receiverName = $receiverName;
    }

    public static function fromEnvelope(Envelope $envelope): self
    {
        /** @var MonitorIdStamp|null $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            throw new \RuntimeException('Envelope should have a MonitorIdStamp!');
        }

        return new self(
            uuid_create(UUID_TYPE_RANDOM),
            $monitorIdStamp->getId(),
            \get_class($envelope->getMessage()),
            \DateTimeImmutable::createFromFormat('U', (string) time())
        );
    }

    public function getId(): string
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

    public function setReceivedAt(\DateTimeImmutable $receivedAt): void
    {
        $this->receivedAt = $receivedAt;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceiverName(string $receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
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