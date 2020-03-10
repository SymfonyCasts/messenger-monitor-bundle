<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Tests\Storage\Doctrine\Driver;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver\MySQLDriver;

final class MySQLDriverTest extends TestCase
{
    public function testGetDateDiffInSecondsExpression(): void
    {
        $mysqlDriver = new MySQLDriver();
        $this->assertSame(
            'TIME_TO_SEC(TIMEDIFF(field_from, field_to))',
            $mysqlDriver->getDateDiffInSecondsExpression('field_from', 'field_to')
        );
    }
}
