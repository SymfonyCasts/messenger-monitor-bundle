<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;

/**
 * @internal
 */
final class FailedMessageRejecter
{
    public function __construct(private FailureReceiverProvider $failureReceiverProvider)
    {
    }

    public function rejectFailedMessage(int $id): void
    {
        $failureReceiver = $this->failureReceiverProvider->getFailureReceiver();

        $envelope = $failureReceiver->find($id);

        if (null === $envelope) {
            throw new \RuntimeException(\sprintf('The message with id "%s" was not found.', $id));
        }

        $failureReceiver->reject($envelope);
    }
}
