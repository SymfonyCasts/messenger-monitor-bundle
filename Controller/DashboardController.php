<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Controller;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRepository;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Twig\Environment;

class DashboardController
{
    private $twig;
    private $receiverLocator;
    private $failedMessageRepository;

    public const FAILURE_RECEIVER_NOT_LISTABLE = 'failure-receiver-not-listable';
    public const NO_FAILURE_RECEIVER           = 'no-failure-receiver';

    public function __construct(
        Environment $twig,
        ReceiverLocator $receiverLocator,
        FailedMessageRepository $failedMessageRepository
    ) {
        $this->twig = $twig;
        $this->receiverLocator = $receiverLocator;
        $this->failedMessageRepository = $failedMessageRepository;
    }

    public function __invoke(): Response
    {
        $transports = [];
        foreach ($this->receiverLocator->getReceiversMapping() as $name => $receiver) {
            $transports[$name] = $receiver instanceof MessageCountAwareInterface ? $receiver->getMessageCount() : null;
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
                    'transports' => $transports,
                    'cannotListFailedMessages' => $cannotListFailedMessages,
                    'failedMessages' => $failedMessages
                ]
            )
        );
    }
}
