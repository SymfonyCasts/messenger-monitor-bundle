<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\FunctionalTests;

final class RejectControllerTest extends AbstractFunctionalTests
{
    public function testRejectFailedMessage(): void
    {
        $envelope = $this->dispatchMessage(true);
        $this->handleMessage($envelope, 'queue');

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', sprintf('/failed-message/reject/%s', $id = $this->getLastFailedMessageId()));
        self::assertResponseIsSuccessful();

        $this->assertQueuesCounts(['queue' => 0, 'failed' => 0], $crawler);
        $this->assertAlertIsPresent($crawler, '.alert-success', sprintf('Message with id "%s" correctly rejected.', $id));
    }
}
