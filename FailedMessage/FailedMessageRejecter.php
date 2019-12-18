<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

/**
 * @internal
 */
class FailedMessageRejecter
{
    private $receiverLocator;
    private $failureReceiverName;

    public function __construct(ReceiverLocator $receiverLocator, FailureReceiverName $failureReceiverName)
    {
        $this->receiverLocator = $receiverLocator;
        $this->failureReceiverName = $failureReceiverName;
    }

    public function rejectFailedMessage($id): void
    {
        if (null === $this->failureReceiverName->toString()) {
            throw new FailureReceiverDoesNotExistException();
        }

        $failureReceiver = $this->receiverLocator->getReceiver($this->failureReceiverName->toString());

        if (!$failureReceiver instanceof ListableReceiverInterface) {
            throw new FailureReceiverNotListableException();
        }

        $envelope = $failureReceiver->find($id);

        if (null === $envelope) {
            throw new \RuntimeException(sprintf('The message with id "%s" was not found.', $id));
        }

        $failureReceiver->reject($envelope);
    }
}
