<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Controller;

use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRepository;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Twig\Environment;

class MessengerMonitorController
{
    private $twig;
    private $receiverLocator;
    private $failedMessageRepository;

    public function __construct(Environment $twig, ReceiverLocator $receiverLocator, FailedMessageRepository $failedMessageRepository)
    {
        $this->twig = $twig;
        $this->receiverLocator = $receiverLocator;
        $this->failedMessageRepository = $failedMessageRepository;
    }

    public function showDashboard(): Response
    {
        $transports = [];
        foreach ($this->receiverLocator->getReceiversMapping() as $name => $receiver) {
            $transports[$name] = $receiver instanceof MessageCountAwareInterface ? $receiver->getMessageCount() : -1;
        }

        try {
            $failedMessages = $this->failedMessageRepository->listFailedMessages();
        } catch (\RuntimeException $exception) {
            $failedMessages = null;
        }

        return new Response(
            $this->twig->render(
                '@KaroIOMessengerMonitor/dashboard.html.twig',
                [
                    'transports' => $transports,
                    'cannotListFailedMessages' => null === $failedMessages,
                    'failedMessages' => $failedMessages
                ]
            )
        );
    }

}
