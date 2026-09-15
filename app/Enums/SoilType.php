<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * What is on the floor. A buyer describes the job this way long before they
 * know which machine class removes it, so this is the first question the
 * advisor asks and the axis the industry pages are organised around.
 */
enum SoilType: string implements HasLabel
{
    case Dust = 'dust';
    case Oil = 'oil';
    case Grease = 'grease';
    case Mud = 'mud';
    case Food = 'food';
    case Chemical = 'chemical';
    case Liquid = 'liquid';

    public function getLabel(): string
    {
        return __('enums.soil_type.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
