<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Controller;

use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRetryer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// todo: bulk treatment
class RetryFailedMessageController
{
    private $failedMessageRetryer;
    private $session;
    private $urlGenerator;

    public function __construct(FailedMessageRetryer $failedMessageRetryer, SessionInterface $session, UrlGeneratorInterface $urlGenerator)
    {
        $this->failedMessageRetryer = $failedMessageRetryer;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke($id): RedirectResponse
    {
        try {
            $this->failedMessageRetryer->retryFailedMessage($id);
            $this->session->getBag('flashes')->add('messenger_monitor.success', sprintf('Message with id "%s" correctly retried.', $id));
        } catch (\Exception $exception) {
            $this->session->getBag('flashes')->add('messenger_monitor.error', sprintf('Error while rejecting message with id "%s": %s', $id, $exception->getMessage()));
        }

        return new RedirectResponse($this->urlGenerator->generate('karo-io.dashboard'));
    }
}
