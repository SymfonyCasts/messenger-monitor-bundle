<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Statistics;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Statistics\DoctrineProcessor;
use SymfonyCasts\MessengerMonitorBundle\Statistics\Statistics;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;

final class DoctrineProcessorTest extends TestCase
{
    public function testCreateStatistics(): void
    {
        $doctrineProcessor = new DoctrineProcessor(
            $doctrineConnection = $this->createMock(Connection::class)
        );

        $doctrineConnection->expects($this->once())
            ->method('getStatistics')
            ->willReturn($statistics = new Statistics(new \DateTimeImmutable(), new \DateTimeImmutable()));

        $this->assertSame($statistics, $doctrineProcessor->createStatistics());
    }
}
