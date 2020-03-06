<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Locator;

use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;
use SymfonyCasts\MessengerMonitorBundle\Exception\ReceiverDoesNotExistException;

/**
 * @internal
 *
 * @final
 */
class ReceiverLocator
{
    private $receiverLocator;
    private $receiverNames;

    public function __construct(ServiceProviderInterface $receiverLocator, array $receiverNames)
    {
        $this->receiverLocator = $receiverLocator;
        $this->receiverNames = $receiverNames;
    }

    /**
     * Key-Value array of receiver name to receiver object.
     *
     * @return ReceiverInterface[]
     */
    public function getReceiversMapping(): array
    {
        $receivers = [];
        foreach ($this->receiverNames as $receiverName) {
            $receivers[$receiverName] = $this->getReceiver($receiverName);
        }

        return $receivers;
    }

    public function getReceiver(string $receiverName): ReceiverInterface
    {
        if (!\in_array($receiverName, $this->receiverNames, true) || !$this->receiverLocator->has($receiverName)) {
            throw new ReceiverDoesNotExistException($receiverName, $this->receiverNames);
        }

        return $this->receiverLocator->get($receiverName);
    }
}
