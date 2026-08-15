<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RentalPeriod: string implements HasLabel
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function getLabel(): string
    {
        return __('enums.rental_period.'.$this->value);
    }

    public function days(): int
    {
        return match ($this) {
            self::Daily => 1,
            self::Weekly => 7,
            self::Monthly => 30,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
