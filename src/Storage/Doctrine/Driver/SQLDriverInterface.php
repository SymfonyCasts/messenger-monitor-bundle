<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Driver;

/**
 * @internal
 */
interface SQLDriverInterface
{
    public function getDateDiffInSecondsExpression(string $fieldFrom, string $fieldTo): string;
}
