<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\FailedMessageRejecter;

/**
 * @internal
 */
final class RejectFailedMessageController
{
    private $failedMessageRejecter;
    private $session;
    private $urlGenerator;

    public function __construct(FailedMessageRejecter $failedMessageRejecter, SessionInterface $session, UrlGeneratorInterface $urlGenerator)
    {
        $this->failedMessageRejecter = $failedMessageRejecter;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(int $id): RedirectResponse
    {
        /** @var FlashBagInterface $sessionBag */
        $sessionBag = $this->session->getBag('flashes');

        try {
            $this->failedMessageRejecter->rejectFailedMessage($id);
            $sessionBag->add('messenger_monitor.success', sprintf('Message with id "%s" correctly rejected.', $id));
        } catch (\Exception $exception) {
            $sessionBag->add('messenger_monitor.error', sprintf('Error while rejecting message with id "%s": %s', $id, $exception->getMessage()));
        }

        return new RedirectResponse($this->urlGenerator->generate('symfonycasts.messenger_monitor.dashboard'));
    }
}
