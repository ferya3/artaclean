<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OperatorType: string implements HasLabel
{
    case WalkBehind = 'walk_behind';
    case RideOn = 'ride_on';
    case Handheld = 'handheld';
    case Stationary = 'stationary';
    case Towed = 'towed';

    public function getLabel(): string
    {
        return __('enums.operator_type.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
