<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\FailedMessageRetryer;

/**
 * @internal
 */
final class RetryFailedMessageController
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

    public function __invoke(int $id): RedirectResponse
    {
        try {
            $this->failedMessageRetryer->retryFailedMessage($id);
            $this->session->getBag('flashes')->add('messenger_monitor.success', sprintf('Message with id "%s" correctly retried.', $id));
        } catch (\Exception $exception) {
            $this->session->getBag('flashes')->add('messenger_monitor.error', sprintf('Error while retrying message with id "%s": %s', $id, $exception->getMessage()));
        }

        return new RedirectResponse($this->urlGenerator->generate('symfonycasts.messenger_monitor.dashboard'));
    }
}
