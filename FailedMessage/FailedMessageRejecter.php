<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\Exception\FailureTransportNotListable;
use KaroIO\MessengerMonitorBundle\Locator\FailureTransportLocator;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class FailedMessageRejecter
{
    private $failureTransportLocator;

    public function __construct(FailureTransportLocator $failureTransportLocator)
    {
        $this->failureTransportLocator = $failureTransportLocator;
    }

    public function rejectFailedMessage($id): void
    {
        $failureTransport = $this->failureTransportLocator->getFailureTransport();
        if (!$failureTransport instanceof ListableReceiverInterface) {
            throw new FailureTransportNotListable();
        }

        $envelope = $failureTransport->find($id);

        if (null === $envelope) {
            throw new \RuntimeException(sprintf('The message with id "%s" was not found.', $id));
        }

        $failureTransport->reject($envelope);
    }
}
