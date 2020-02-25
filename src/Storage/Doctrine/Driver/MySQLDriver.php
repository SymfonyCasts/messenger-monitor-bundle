<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage\Doctrine\Driver;

/**
 * @internal
 */
final class MySQLDriver implements SQLDriver
{
    public function getDateDiffInSecondsExpression(string $fieldFrom, string $fieldTo): string
    {
        return sprintf('TIME_TO_SEC(TIMEDIFF(%s, %s))', $fieldFrom, $fieldTo);
    }
}
