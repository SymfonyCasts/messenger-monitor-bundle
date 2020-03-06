<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailureReceiver;

use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use SymfonyCasts\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use SymfonyCasts\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use SymfonyCasts\MessengerMonitorBundle\Locator\ReceiverLocator;

/**
 * @final
 *
 * @internal
 */
class FailureReceiverProvider
{
    private $receiverLocator;
    private $failureReceiverName;

    public function __construct(ReceiverLocator $receiverLocator, FailureReceiverName $failureReceiverName)
    {
        $this->receiverLocator = $receiverLocator;
        $this->failureReceiverName = $failureReceiverName;
    }

    public function getFailureReceiver(): ListableReceiverInterface
    {
        if (null === $this->failureReceiverName->toString()) {
            throw new FailureReceiverDoesNotExistException();
        }

        $failureReceiver = $this->receiverLocator->getReceiver($this->failureReceiverName->toString());

        if (!$failureReceiver instanceof ListableReceiverInterface) {
            throw new FailureReceiverNotListableException();
        }

        return $failureReceiver;
    }
}
