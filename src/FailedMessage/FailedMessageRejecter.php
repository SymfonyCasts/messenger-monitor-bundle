<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;

/**
 * @internal
 */
class FailedMessageRejecter
{
    private $failureReceiverProvider;

    public function __construct(FailureReceiverProvider $failureReceiverProvider)
    {
        $this->failureReceiverProvider = $failureReceiverProvider;
    }

    public function rejectFailedMessage($id): void
    {
        $failureReceiver = $this->failureReceiverProvider->getFailureReceiver();

        $envelope = $failureReceiver->find($id);

        if (null === $envelope) {
            throw new \RuntimeException(sprintf('The message with id "%s" was not found.', $id));
        }

        $failureReceiver->reject($envelope);
    }
}
