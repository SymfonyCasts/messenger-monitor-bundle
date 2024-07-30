<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use SymfonyCasts\MessengerMonitorBundle\Stamp\MonitorIdStamp;

/**
 * @internal
 *
 * @final
 */
class StoredMessageProvider
{
    public function __construct(private Connection $doctrineConnection, private ?LoggerInterface $logger = null)
    {
    }

    public function getStoredMessage(Envelope $envelope): ?StoredMessage
    {
        /** @var MonitorIdStamp|null $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            $this->logError('Envelope should have a MonitorIdStamp!');

            return null;
        }

        $storedMessage = $this->doctrineConnection->findMessage($monitorIdStamp->getId());

        if (null === $storedMessage) {
            $this->logError(\sprintf('Message with id "%s" not found', $monitorIdStamp->getId()));

            return null;
        }

        return $storedMessage;
    }

    private function logError(string $message): void
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->error($message);
    }
}
