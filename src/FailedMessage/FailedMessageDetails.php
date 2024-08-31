<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

/**
 * @internal
 */
final class FailedMessageDetails
{
    public function __construct(
        private mixed $id,
        private string $class,
        private string $failedAt,
        private ?string $error,
    ) {
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFailedAt(): string
    {
        return $this->failedAt;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
