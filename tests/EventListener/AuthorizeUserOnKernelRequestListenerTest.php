<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use SymfonyCasts\MessengerMonitorBundle\EventListener\AuthorizeUserOnKernelRequestListener;

final class AuthorizeUserOnKernelRequestListenerTest extends TestCase
{
    public function testOnKernelRequestAllowedForOtherRoutes(): void
    {
        $authorizeUserOnKernelRequest = new AuthorizeUserOnKernelRequestListener(
            $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class)
        );

        $authorizationChecker->expects($this->never())->method('isGranted');

        $authorizeUserOnKernelRequest->onKernelRequest(
            new RequestEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request([], [], ['_route' => 'foo']),
                HttpKernelInterface::MASTER_REQUEST
            )
        );
    }

    public function testOnKernelRequestAllowedForAuthorizedUser(): void
    {
        $authorizeUserOnKernelRequest = new AuthorizeUserOnKernelRequestListener(
            $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class)
        );

        $authorizationChecker->expects($this->once())->method('isGranted')->with('ROLE_MESSENGER_ADMIN')->willReturn(true);

        $authorizeUserOnKernelRequest->onKernelRequest(
            new RequestEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request([], [], ['_route' => 'symfonycasts.messenger_monitor.awesome_route']),
                HttpKernelInterface::MASTER_REQUEST
            )
        );
    }

    public function testOnKernelRequestForbiddenForUnauthorizedUser(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $authorizeUserOnKernelRequest = new AuthorizeUserOnKernelRequestListener(
            $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class)
        );

        $authorizationChecker->expects($this->once())->method('isGranted')->with('ROLE_MESSENGER_ADMIN')->willReturn(false);

        $authorizeUserOnKernelRequest->onKernelRequest(
            new RequestEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request([], [], ['_route' => 'symfonycasts.messenger_monitor.awesome_route']),
                HttpKernelInterface::MASTER_REQUEST
            )
        );
    }
}
