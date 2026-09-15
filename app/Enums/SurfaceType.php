<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * What is being cleaned. Surface decides the machine family before any
 * figure does — no amount of productivity makes a scrubber right for a
 * carpet, or an extractor right for an asphalt yard.
 */
enum SurfaceType: string implements HasLabel
{
    case Concrete = 'concrete';
    case Epoxy = 'epoxy';
    case Tile = 'tile';
    case Carpet = 'carpet';
    case Upholstery = 'upholstery';
    case Machinery = 'machinery';
    case Vertical = 'vertical';
    case Outdoor = 'outdoor';

    public function getLabel(): string
    {
        return __('enums.surface_type.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
