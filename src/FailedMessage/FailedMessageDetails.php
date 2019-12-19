<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

/**
 * @internal
 */
class FailedMessageDetails
{
    private $id;
    private $class;
    private $failedAt;
    private $error;

    public function __construct($id, string $class, string $failedAt, string $error)
    {
        $this->id = $id;
        $this->class = $class;
        $this->failedAt = $failedAt;
        $this->error = $error;
    }

    public function getId()
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

    public function getError(): string
    {
        return $this->error;
    }
}
