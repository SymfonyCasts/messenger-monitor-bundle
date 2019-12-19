<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailureReceiver;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

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
