<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Locator;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

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
            $message = sprintf('The receiver "%s" does not exist.', $receiverName);
            if ($this->receiverNames) {
                $message .= sprintf(' Valid receivers are: %s.', implode(', ', $this->receiverNames));
            }

            throw new RuntimeException($message);
        }

        return $this->receiverLocator->get($receiverName);
    }
}
