<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Exception;

use Symfony\Component\Messenger\Exception\RuntimeException;

/**
 * @internal
 */
class ReceiverDoesNotExistException extends RuntimeException
{
    public function __construct(string $receiverName, array $availableReceivers = [])
    {
        $message = sprintf('The receiver "%s" does not exist.', $receiverName);
        if (\count($availableReceivers)) {
            $message .= sprintf(' Valid receivers are: %s.', implode(', ', $availableReceivers));
        }

        parent::__construct($message);
    }
}
