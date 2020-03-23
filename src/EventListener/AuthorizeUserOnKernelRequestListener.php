<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @internal
 */
final class AuthorizeUserOnKernelRequestListener implements EventSubscriberInterface
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if (0 !== strpos($request->attributes->get('_route'), 'symfonycasts.messenger_monitor.')) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_MESSENGER_ADMIN')) {
            return;
        }

        throw new AccessDeniedHttpException('Role "ROLE_MESSENGER_ADMIN" is needed to access messenger monitor bundle routes.');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
