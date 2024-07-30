<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

final class RejectFailedMessageControllerTest extends AbstractFunctionalTests
{
    public function testRejectFailedMessageSuccess(): void
    {
        $envelope = $this->dispatchMessage(true);
        $this->handleMessage($envelope, 'queue');

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', \sprintf('/failed-message/reject/%s', $id = $this->getLastFailedMessageId()));
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent($crawler, '.alert-success', \sprintf('Message with id "%s" correctly rejected.', $id));
    }

    public function testRejectFailedMessageFails(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/failed-message/reject/123');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent(
            $crawler,
            '.alert-danger',
            'Error while rejecting message with id "123": The message with id "123" was not found.'
        );
    }
}
