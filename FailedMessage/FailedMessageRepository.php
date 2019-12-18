<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\Exception\FailureTransportNotListable;
use KaroIO\MessengerMonitorBundle\Locator\FailureTransportLocator;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

// all this code was stolen from \Symfony\Component\Messenger\Command\FailedMessagesShowCommand
// todo: find a better name?
class FailedMessageRepository
{
    private $receiverLocator;
    private $failureReceiverName;

    public function __construct(ReceiverLocator $receiverLocator, string $failureReceiverName)
    {
        $this->receiverLocator = $receiverLocator;
        $this->failureReceiverName = $failureReceiverName;
    }

    public function listFailedMessages(): array
    {
        $failureReceiver = $this->receiverLocator->getReceiver($this->failureReceiverName);
        if (!$failureReceiver instanceof ListableReceiverInterface) {
            throw new FailureTransportNotListable();
        }

        // todo: this number should be dynamic
        $envelopes = $failureReceiver->all(10);

        $rows = [];
        foreach ($envelopes as $envelope) {
            $lastRedeliveryStampWithException = $this->getLastRedeliveryStampWithException($envelope);

            $rows[] = [
                'id' => $this->getMessageId($envelope),
                'class' => \get_class($envelope->getMessage()),
                'failedAt' => null === $lastRedeliveryStampWithException ? '' : $lastRedeliveryStampWithException->getRedeliveredAt()->format('Y-m-d H:i:s'),
                'error' => null === $lastRedeliveryStampWithException ? '' : $lastRedeliveryStampWithException->getExceptionMessage(),
            ];
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
        /** @var TransportMessageIdStamp $stamp */
        $stamp = $envelope->last(TransportMessageIdStamp::class);

        return null !== $stamp ? $stamp->getId() : null;
    }
}
