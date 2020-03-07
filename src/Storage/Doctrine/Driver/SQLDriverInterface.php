<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver;

/**
 * todo: implement for PostgreSQL
 * @internal
 */
interface SQLDriverInterface
{
    public function getDateDiffInSecondsExpression(string $fieldFrom, string $fieldTo): string;
}
