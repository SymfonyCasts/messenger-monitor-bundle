<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\Exception\FailureTransportNotListable;
use KaroIO\MessengerMonitorBundle\Locator\FailureTransportLocator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\SingleMessageReceiver;
use Symfony\Component\Messenger\Worker;

// all this code was stolen from \Symfony\Component\Messenger\Command\FailedMessagesRetryCommand
class FailedMessageRetryer
{
    private $failureTransportLocator;
    private $eventDispatcher;
    private $messageBus;
    private $logger;

    public function __construct(FailureTransportLocator $failureTransportLocator, MessageBusInterface $messageBus, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->failureTransportLocator = $failureTransportLocator;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function retryFailedMessage($id): void
    {
        $this->eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(1));

        $failureTransport = $this->failureTransportLocator->getFailureTransport();
        if (!$failureTransport instanceof ListableReceiverInterface) {
            throw new FailureTransportNotListable();
        }

        $envelope = $failureTransport->find($id);
        if (null === $envelope) {
            throw new \RuntimeException(sprintf('The message "%s" was not found.', $id));
        }

        $singleReceiver = new SingleMessageReceiver($failureTransport, $envelope);
        $worker = new Worker(
            [$this->failureTransportLocator->getFailureTransportName() => $singleReceiver],
            $this->messageBus,
            $this->eventDispatcher,
            $this->logger
        );
        $worker->run();
    }
}
