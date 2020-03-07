<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\FailedMessage;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\SingleMessageReceiver;
use Symfony\Component\Messenger\Worker;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use SymfonyCasts\MessengerMonitorBundle\FailureReceiver\FailureReceiverProvider;

/**
 * all this code was stolen from \Symfony\Component\Messenger\Command\FailedMessagesRetryCommand.
 *
 * @internal
 */
final class FailedMessageRetryer
{
    private $failureReceiverProvider;
    private $failureReceiverName;
    private $eventDispatcher;
    private $messageBus;
    private $logger;

    public function __construct(FailureReceiverProvider $failureReceiverProvider, FailureReceiverName $failureReceiverName, MessageBusInterface $messageBus, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->failureReceiverProvider = $failureReceiverProvider;
        $this->failureReceiverName = $failureReceiverName;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function retryFailedMessage(int $id): void
    {
        $this->eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(1));

        $failureReceiver = $this->failureReceiverProvider->getFailureReceiver();

        $envelope = $failureReceiver->find($id);
        if (null === $envelope) {
            throw new \RuntimeException(sprintf('The message "%s" was not found.', $id));
        }

        $singleReceiver = new SingleMessageReceiver($failureReceiver, $envelope);
        $worker = new Worker(
            [$this->failureReceiverName->toString() => $singleReceiver],
            $this->messageBus,
            $this->eventDispatcher,
            $this->logger
        );
        $worker->run();
    }
}
