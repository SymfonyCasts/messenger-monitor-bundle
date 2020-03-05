<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Controller;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRepository;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use KaroIO\MessengerMonitorBundle\Statistics\StatisticsProcessor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Twig\Environment;

/**
 * @internal
 */
final class DashboardController
{
    private $twig;
    private $receiverLocator;
    private $failedMessageRepository;
    private $statisticsProcessor;

    public const FAILURE_RECEIVER_NOT_LISTABLE = 'failure-receiver-not-listable';
    public const NO_FAILURE_RECEIVER = 'no-failure-receiver';

    public function __construct(
        Environment $twig,
        ReceiverLocator $receiverLocator,
        FailedMessageRepository $failedMessageRepository,
        StatisticsProcessor $statisticsProcessor
    ) {
        $this->twig = $twig;
        $this->receiverLocator = $receiverLocator;
        $this->failedMessageRepository = $failedMessageRepository;
        $this->statisticsProcessor = $statisticsProcessor;
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
        } catch (FailureReceiverNotListableException $exception) {
            $cannotListFailedMessages = self::FAILURE_RECEIVER_NOT_LISTABLE;
        } catch (FailureReceiverDoesNotExistException $exception) {
            $cannotListFailedMessages = self::NO_FAILURE_RECEIVER;
        }

        return new Response(
            $this->twig->render(
                '@KaroIOMessengerMonitor/dashboard.html.twig',
                [
                    'receivers'                => $receivers,
                    'cannotListFailedMessages' => $cannotListFailedMessages,
                    'failedMessages'           => $failedMessages,
                    'statistics'               => $this->statisticsProcessor->processStatistics(),
                ]
            )
        );
    }
}
