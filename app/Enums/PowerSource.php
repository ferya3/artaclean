<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PowerSource: string implements HasLabel
{
    case Electric = 'electric';
    case Battery = 'battery';
    case Petrol = 'petrol';
    case Diesel = 'diesel';
    case Lpg = 'lpg';
    case Pneumatic = 'pneumatic';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return __('enums.power_source.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
