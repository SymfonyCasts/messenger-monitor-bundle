<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\EventListener;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\FailedMessage\RetriedMessageEvent;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener\SaveRetriedMessageListener;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\StoredMessage;
use SymfonyCasts\MessengerMonitorBundle\Tests\TestableMessage;

final class SaveRetriedMessageListenerTest extends TestCase
{
    public function testStoreInDoctrineOnMessageSent(): void
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

        $listener->onMessageRetried(new RetriedMessageEvent('message_uid', TestableMessage::class));
    }
}
