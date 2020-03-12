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
    public function __construct(
        private string $messageUid,
        private string $messageClass,
        private \DateTimeImmutable $dispatchedAt,
        private ?int $id = null,
        private ?float $waitingTime = null,
        private ?string $receiverName = null,
        private ?float $handlingTime = null,
        private ?float $failingTime = null
    ) {
    }

    public static function fromEnvelope(Envelope $envelope): self
    {
        /** @var MonitorIdStamp|null $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            throw new MessengerIdStampMissingException();
        }

        /** @psalm-suppress PossiblyFalseArgument */
        return new self(
            $monitorIdStamp->getId(),
            $envelope->getMessage()::class,
            \DateTimeImmutable::createFromFormat('0.u00 U', microtime())
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

    public function getWaitingTime(): ?float
    {
        return $this->waitingTime;
    }

    /**
     * @param float $delay The delay in seconds
     */
    public function updateWaitingTime(float $delay = 0): void
    {
        $now = \DateTimeImmutable::createFromFormat('0.u00 U', microtime());
        /** @psalm-suppress PossiblyFalseReference */
        $this->waitingTime = round((float) $now->format('U.u') - (float) $this->dispatchedAt->format('U.u'), 6) - $delay;
    }

    public function setReceiverName(string $receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
    }

    public function getHandlingTime(): ?float
    {
        return $this->handlingTime;
    }

    public function updateHandlingTime(): void
    {
        $this->handlingTime = $this->computePassedTimeSinceReceived();
    }

    public function getFailingTime(): ?float
    {
        return $this->failingTime;
    }

    public function updateFailingTime(): void
    {
        $this->failingTime = $this->computePassedTimeSinceReceived();
    }

    private function computePassedTimeSinceReceived(): float
    {
        $now = \DateTimeImmutable::createFromFormat('0.u00 U', microtime());

        /** @psalm-suppress PossiblyFalseReference */
        return round(
            (float) $now->format('U.u')
            - (float) $this->dispatchedAt->format('U.u')
            - $this->waitingTime,
            6
        );
    }
}
