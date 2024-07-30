<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TimeDisplayExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time', [$this, 'formatTime']),
        ];
    }

    public function formatTime(float $seconds): string
    {
        if ($seconds < 10) {
            $seconds = round($seconds, 2);

            return \sprintf('%s %s', $seconds, $this->pluralize('second', $seconds));
        }

        if ($seconds < 60) {
            return \sprintf('%d seconds', round($seconds));
        }

        $minutes = (int) floor($seconds / 60);
        $intSeconds = (int) $seconds % 60;

        if (0 === $intSeconds) {
            return \sprintf('%d %s', $minutes, $this->pluralize('minute', $minutes));
        }

        return \sprintf('%d %s %d %s', $minutes, $this->pluralize('minute', $minutes), $intSeconds, $this->pluralize('second', $intSeconds));
    }

    private function pluralize(string $word, float $number): string
    {
        return $word.(1.0 === $number ? '' : 's');
    }
}
