<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

final class RetryFailedMessageControllerTest extends AbstractFunctionalTests
{
    public function testRetryFailedMessage(): void
    {
        $envelope = $this->dispatchMessage(true);
        $this->handleMessage($envelope, 'queue');

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', \sprintf('/failed-message/retry/%s', $id = $this->getLastFailedMessageId()));
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent($crawler, '.alert-success', \sprintf('Message with id "%s" correctly retried.', $id));
    }

    public function testRetryFailedMessageFails(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/failed-message/retry/123');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent(
            $crawler,
            '.alert-danger',
            'Error while retrying message with id "123": The message "123" was not found.'
        );
    }
}
