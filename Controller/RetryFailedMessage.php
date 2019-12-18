<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Controller;

use KaroIO\MessengerMonitorBundle\FailedMessage\FailedMessageRetryer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RetryFailedMessage
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
            $this->session->getBag('flashes')->add('success', sprintf('Message with "%s" correctly sent to transport.', $id));
        } catch (\Exception $exception) {
            throw $exception;
            $this->session->getBag('flashes')->add('danger', sprintf('Error while retrying message with "%s": %s', $id, $exception->getMessage()));
        }

        return new RedirectResponse($this->urlGenerator->generate('karo-io.dashboard'));
    }
}
