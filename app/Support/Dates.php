<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeInterface;
use IntlDateFormatter;

/**
 * Dates as the reader's calendar writes them.
 *
 * A Persian page showing "۲۹ ژوئن ۲۰۲۶" is a translated Gregorian date, not a
 * Persian one, and to an Iranian buyer it reads as a foreign site with the
 * labels swapped. ICU carries the Jalali calendar, so the same instant is
 * formatted as ۸ تیر ۱۴۰۵ for `fa` and 29 June 2026 for everyone else.
 */
final class Dates
{
    public static function long(?DateTimeInterface $date, ?string $locale = null): string
    {
        if ($date === null) {
            return '';
        }

        $locale = $locale ?? app()->getLocale();

        $formatter = new IntlDateFormatter(
            $locale === 'fa' ? 'fa_IR@calendar=persian' : $locale,
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            config('app.timezone'),
            IntlDateFormatter::TRADITIONAL,
        );

        return $formatter->format($date) ?: '';
    }
}
