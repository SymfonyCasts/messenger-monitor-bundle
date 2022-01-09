<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;

/**
 * all this code was stolen from \Symfony\Component\Messenger\Command\FailedMessagesShowCommand.
 *
 * @internal
 */
final class FailedMessageRepository
{
    public function __construct(private FailureReceiverProvider $failureReceiverProvider)
    {
    }

    /**
     * @return FailedMessageDetails[]
     */
    public function listFailedMessages(): array
    {
        $envelopes = $this->failureReceiverProvider->getFailureReceiver()->all(10);

        $rows = [];
        foreach ($envelopes as $envelope) {
            /** @var RedeliveryStamp|null $lastRedeliveryStamp */
            $lastRedeliveryStamp = $envelope->last(RedeliveryStamp::class);
            /** @var ErrorDetailsStamp|null $lastErrorDetailsStamp */
            $lastErrorDetailsStamp = $envelope->last(ErrorDetailsStamp::class);

            $rows[] = new FailedMessageDetails(
                $this->getMessageId($envelope),
                $envelope->getMessage()::class,
                null !== $lastRedeliveryStamp ? $lastRedeliveryStamp->getRedeliveredAt()->format('Y-m-d H:i:s') : '',
                null !== $lastErrorDetailsStamp ? $lastErrorDetailsStamp->getExceptionMessage() : ''
            );
        }

        return $rows;
    }

    private function getMessageId(Envelope $envelope): mixed
    {
        /** @var TransportMessageIdStamp|null $stamp */
        $stamp = $envelope->last(TransportMessageIdStamp::class);

        return $stamp?->getId();
    }
}
