<?php

namespace KaroIO\MessengerMonitorBundle\Controller;

use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Twig\Environment;

class MessengerMonitorController
{
    private $twig;
    private $receiverLocator;

    public function __construct(Environment $twig, ReceiverLocator $receiverLocator)
    {
        $this->twig = $twig;
        $this->receiverLocator = $receiverLocator;
    }

    public function showDashboard()
    {
        $transports = [];
        foreach ($this->receiverLocator->getReceiverMapping() as $name => $receiver) {
            $transports[$name] = $receiver instanceof MessageCountAwareInterface ? $receiver->getMessageCount() : -1;
        }

        return new Response($this->twig->render('@KaroIOMessengerMonitor/dashboard.html.twig', ['transports' => $transports]));
    }

}
