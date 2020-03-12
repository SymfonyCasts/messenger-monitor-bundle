<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\FailureMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\RetryableMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

/** @group functional */
final class DashboardControllerTest extends AbstractFunctionalTests
{
    public function testDashboardEmpty(): void
    {
        $this->assertDisplayedQueuesOnDashboard(['queue' => 0, 'queue_with_retry' => 0, 'failed' => 0]);
    }

    public function testDashboardWithForbiddenUser(): void
    {
        $this->client->request('GET', '/', [], [], ['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'password']);
        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDashboardWithOneQueuedMessage(): void
    {
        $envelope = $this->dispatchMessage(new TestableMessage());

        $this->assertDisplayedQueuesOnDashboard(['queue' => 1, 'queue_with_retry' => 0, 'failed' => 0]);
        $this->assertStoredMessageIsInDB($envelope);

        $this->handleLastMessageInQueue('queue');
        $this->assertDisplayedQueuesOnDashboard(['queue' => 0, 'queue_with_retry' => 0, 'failed' => 0]);
    }

    public function testDashboardWithOneFailedMessage(): void
    {
        $envelope = $this->dispatchMessage(new FailureMessage());
        $this->handleLastMessageInQueue('queue');

        $this->assertDisplayedQueuesOnDashboard(['queue' => 0, 'queue_with_retry' => 0, 'failed' => 1]);
        $this->assertStoredMessageIsInDB($envelope);
    }

    public function testDashboardWithOneRetryableMessage(): void
    {
        $envelope = $this->dispatchMessage(new RetryableMessage());

        $this->assertDisplayedQueuesOnDashboard(['queue' => 0, 'queue_with_retry' => 1, 'failed' => 0]);
        $this->assertStoredMessageIsInDB($envelope);

        $this->handleLastMessageInQueue('queue_with_retry');
        $this->assertDisplayedQueuesOnDashboard(['queue' => 0, 'queue_with_retry' => 1, 'failed' => 0]);
        $this->assertStoredMessageIsInDB($envelope, 2);

        $this->handleLastMessageInQueue('queue_with_retry');
        $this->assertDisplayedQueuesOnDashboard(['queue' => 0, 'queue_with_retry' => 0, 'failed' => 1]);
        $this->assertStoredMessageIsInDB($envelope, 2);
    }

    private function assertDisplayedQueuesOnDashboard(array $queues): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $this->assertQueuesCounts($queues, $crawler);
        $this->assertFailedMessagesCount($queues['failed'], $crawler);
    }
}
