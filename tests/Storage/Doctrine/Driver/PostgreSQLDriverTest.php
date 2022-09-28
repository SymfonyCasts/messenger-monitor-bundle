<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\Driver;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver\PostgreSQLDriver;

final class PostgreSQLDriverTest extends TestCase
{
    public function testGetDateDiffInSecondsExpression(): void
    {
        $mysqlDriver = new PostgreSQLDriver();
        $this->assertSame(
            'extract(epoch from (field_from - field_to))',
            $mysqlDriver->getDateDiffInSecondsExpression('field_from', 'field_to')
        );
    }
}
