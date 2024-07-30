<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver;

/**
 * @internal
 */
final class PostgreSQLDriver implements SQLDriverInterface
{
    public function getDateDiffInSecondsExpression(string $fieldFrom, string $fieldTo): string
    {
        return \sprintf('extract(epoch from (%s - %s))', $fieldFrom, $fieldTo);
    }
}
