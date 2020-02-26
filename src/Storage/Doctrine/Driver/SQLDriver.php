<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Storage\Doctrine\Driver;

/**
 * todo: implement for PostgreSQL
 * @internal
 */
interface SQLDriver
{
    public function getDateDiffInSecondsExpression(string $fieldFrom, string $fieldTo): string;
}
