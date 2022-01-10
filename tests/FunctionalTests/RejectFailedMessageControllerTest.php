<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\FailureMessage;

/** @group functional */
final class RejectFailedMessageControllerTest extends AbstractFunctionalTests
{
    public function testRejectFailedMessageSuccess(): void
    {
        $envelope = $this->dispatchMessage(new FailureMessage());
        $this->handleLastMessageInQueue('queue');

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', sprintf('/failed-message/reject/%s', $id = $this->getLastFailedMessageId()));
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'queue_with_retry' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent($crawler, '.alert-success', sprintf('Message with id "%s" correctly rejected.', $id));
    }

    public function testRejectFailedMessageFails(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/failed-message/reject/123');
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'queue_with_retry' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent(
            $crawler,
            '.alert-danger',
            'Error while rejecting message with id "123": The message with id "123" was not found.'
        );
    }
}
