<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The four families the mega menu groups categories into. Ten categories in
 * one flat list is ten headings to read; four groups of two or three is one
 * glance.
 */
enum NavGroup: string implements HasLabel
{
    case Floor = 'floor';
    case Pressure = 'pressure';
    case Specialist = 'specialist';
    case Consumable = 'consumable';

    public function getLabel(): string
    {
        return __('enums.nav_group.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
