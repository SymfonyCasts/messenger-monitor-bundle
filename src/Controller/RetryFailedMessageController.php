<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\FailedMessageRetryer;

/**
 * @internal
 */
final class RetryFailedMessageController
{
    public function __construct(
        private FailedMessageRetryer $failedMessageRetryer,
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(int $id): RedirectResponse
    {
        /** @var FlashBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('flashes');

        try {
            $this->failedMessageRetryer->retryFailedMessage($id);
            $sessionBag->add('messenger_monitor.success', \sprintf('Message with id "%s" correctly retried.', $id));
        } catch (\Exception $exception) {
            $sessionBag->add('messenger_monitor.error', \sprintf('Error while retrying message with id "%s": %s', $id, $exception->getMessage()));
        }

        return new RedirectResponse($this->urlGenerator->generate('symfonycasts.messenger_monitor.dashboard'));
    }
}
