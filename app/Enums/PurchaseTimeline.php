<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * When the money is available. The single most useful thing a salesperson can
 * know before the first call, and the reason a quote queue can be worked in
 * an order that means something.
 */
enum PurchaseTimeline: string implements HasLabel
{
    case Immediate = 'immediate';
    case Quarter = 'quarter';
    case Year = 'year';
    case Researching = 'researching';

    public function getLabel(): string
    {
        return __('enums.purchase_timeline.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
