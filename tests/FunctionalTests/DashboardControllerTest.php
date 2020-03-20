<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

final class DashboardControllerTest extends AbstractFunctionalTests
{
    public function testDashboardEmpty(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertFailedMessagesCount(0, $crawler);
    }

    public function testDashboardWithOneQueuedMessage(): void
    {
        $envelope = $this->dispatchMessage();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 1, 'failed' => 0], $crawler);
        $this->assertFailedMessagesCount(1, $crawler);
        $this->assertStoredMessageIsInDB($envelope);

        $this->handleMessage($envelope, 'queue');
        $this->testDashboardEmpty();
    }

    public function testDashboardWithOneFailedMessage(): void
    {
        $envelope = $this->dispatchMessage(true);
        $this->handleMessage($envelope, 'queue');

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $this->assertQueuesCounts(['queue' => 0, 'failed' => 1], $crawler);
    }
}
