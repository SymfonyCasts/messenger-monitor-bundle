<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Locator;

use KaroIO\MessengerMonitorBundle\Exception\ReceiverDoesNotExistException;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @internal
 */
class ReceiverLocator
{
    private $receiverLocator;
    private $receiverNames;

    public function __construct(ServiceProviderInterface $receiverLocator, $receiverNames = [])
    {
        $this->receiverLocator = $receiverLocator;
        $this->receiverNames = $receiverNames;
    }

    /**
     * Key-Value array of receiver name to receiver object
     *
     * @return TransportInterface[]
     */
    public function getReceiversMapping(): array
    {
        $receivers = [];
        foreach ($this->receiverNames as $receiverName) {
            $receivers[$receiverName] = $this->getReceiver($receiverName);
        }

        return $receivers;
    }

    public function getReceiver(string $receiverName): TransportInterface
    {
        if (!$this->receiverLocator->has($receiverName)) {
            throw new ReceiverDoesNotExistException($receiverName, $this->receiverNames);
        }

        return $this->receiverLocator->get($receiverName);
    }
}
