<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use SymfonyCasts\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use SymfonyCasts\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\FailedMessageRepository;
use SymfonyCasts\MessengerMonitorBundle\Locator\ReceiverLocator;
use SymfonyCasts\MessengerMonitorBundle\Statistics\StatisticsProcessorInterface;
use Twig\Environment;

/**
 * @internal
 */
final class DashboardController
{
    public const FAILURE_RECEIVER_NOT_LISTABLE = 'failure-receiver-not-listable';
    public const NO_FAILURE_RECEIVER = 'no-failure-receiver';

    public function __construct(
        private Environment $twig,
        private ReceiverLocator $receiverLocator,
        private FailedMessageRepository $failedMessageRepository,
        private StatisticsProcessorInterface $statisticsProcessor,
    ) {
    }

    public function __invoke(): Response
    {
        $receivers = [];
        foreach ($this->receiverLocator->getReceiversMapping() as $name => $receiver) {
            $receivers[$name] = $receiver instanceof MessageCountAwareInterface ? $receiver->getMessageCount() : null;
        }

        $failedMessages = null;
        try {
            $failedMessages = $this->failedMessageRepository->listFailedMessages();
            $cannotListFailedMessages = null;
        } catch (FailureReceiverNotListableException) {
            $cannotListFailedMessages = self::FAILURE_RECEIVER_NOT_LISTABLE;
        } catch (FailureReceiverDoesNotExistException) {
            $cannotListFailedMessages = self::NO_FAILURE_RECEIVER;
        }

        return new Response(
            $this->twig->render(
                '@SymfonyCastsMessengerMonitor/dashboard.html.twig',
                [
                    'receivers' => $receivers,
                    'cannotListFailedMessages' => $cannotListFailedMessages,
                    'failedMessages' => $failedMessages,
                    'statistics' => $this->statisticsProcessor->createStatistics(),
                ]
            )
        );
    }
}
