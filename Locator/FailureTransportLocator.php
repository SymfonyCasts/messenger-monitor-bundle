<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Locator;

use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class FailureTransportLocator
{
    private $receiverLocator;
    private $failureReceiverName;

    public function __construct(ServiceProviderInterface $receiverLocator, string $failureReceiverName)
    {
        $this->receiverLocator = $receiverLocator;
        $this->failureReceiverName = $failureReceiverName;
    }

    public function getFailureTransport(): TransportInterface
    {
        if (!$this->receiverLocator->has($this->failureReceiverName)) {
            throw new \LogicException(sprintf('Failure receiver with name "%s" does not exist.', $this->failureReceiverName));
        }

        return $this->receiverLocator->get($this->failureReceiverName);
    }

}
