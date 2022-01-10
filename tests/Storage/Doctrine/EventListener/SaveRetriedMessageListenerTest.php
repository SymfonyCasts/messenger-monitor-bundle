<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\MessageRetriedByUserEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener\SaveRetriedMessageListener;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\Fixtures\TestableMessage;

final class SaveRetriedMessageListenerTest extends TestCase
{
    public function testOnMessageRetriedStoresAnotherMessage(): void
    {
        $listener = new SaveRetriedMessageListener(
            $doctrineConnection = $this->createMock(Connection::class)
        );

        $doctrineConnection->expects($this->once())
            ->method('saveMessage')
            ->with(
                $this->callback(
                    static function (StoredMessage $storedMessage): bool {
                        return 'message_uid' === $storedMessage->getMessageUid()
                            && TestableMessage::class === $storedMessage->getMessageClass();
                    }
                )
            );

        $listener->onMessageRetried(new MessageRetriedByUserEvent('message_uid', TestableMessage::class));
    }
}
