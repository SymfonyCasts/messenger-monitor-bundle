<?php


namespace Karo\MessengerMonitor\Locator;

use Symfony\Component\Console\Exception\RuntimeException;

class ReceiverLocator
{
    private $receiverLocator;
    private $receiverNames;

    public function __construct($receiverLocator, $receiverNames)
    {
        $this->receiverLocator = $receiverLocator;
        $this->receiverNames = $receiverNames;
    }

    /**
     * Key-Value array of receiver name to receiver object
     *
     * @return array
     */
    public function getReceiverMapping()
    {
        $receivers = [];
        foreach ($this->receiverNames as $receiverName) {
            if (!$this->receiverLocator->has($receiverName)) {
                $message = sprintf('The receiver "%s" does not exist.', $receiverName);
                if ($this->receiverNames) {
                    $message .= sprintf(' Valid receivers are: %s.', implode(', ', $this->receiverNames));
                }

                throw new RuntimeException($message);
            }

            $receivers[$receiverName] = $this->receiverLocator->get($receiverName);
        }

        return $receivers;
    }
}
