<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

use Symfony\Component\Messenger\Envelope;
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
    private $failureReceiverProvider;

    public function __construct(FailureReceiverProvider $failureReceiverProvider)
    {
        $this->failureReceiverProvider = $failureReceiverProvider;
    }

    /**
     * @return FailedMessageDetails[]
     */
    public function listFailedMessages(): array
    {
        $envelopes = $this->failureReceiverProvider->getFailureReceiver()->all(10);

        $rows = [];
        foreach ($envelopes as $envelope) {
            $lastRedeliveryStampWithException = $this->getLastRedeliveryStampWithException($envelope);

            $rows[] = new FailedMessageDetails(
                $this->getMessageId($envelope),
                \get_class($envelope->getMessage()),
                null === $lastRedeliveryStampWithException ? '' : $lastRedeliveryStampWithException->getRedeliveredAt()->format('Y-m-d H:i:s'),
                null === $lastRedeliveryStampWithException ? '' : $lastRedeliveryStampWithException->getExceptionMessage()
            );
        }

        return $rows;
    }

    private function getLastRedeliveryStampWithException(Envelope $envelope): ?RedeliveryStamp
    {
        /** @var RedeliveryStamp $stamp */
        foreach (array_reverse($envelope->all(RedeliveryStamp::class)) as $stamp) {
            if (null !== $stamp->getExceptionMessage()) {
                return $stamp;
            }
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    private function getMessageId(Envelope $envelope)
    {
        /** @var TransportMessageIdStamp|null $stamp */
        $stamp = $envelope->last(TransportMessageIdStamp::class);

        return null !== $stamp ? $stamp->getId() : null;
    }
}
