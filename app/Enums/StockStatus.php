<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockStatus: string implements HasColor, HasLabel
{
    case InStock = 'in_stock';
    case OnOrder = 'on_order';
    case OutOfStock = 'out_of_stock';
    case Discontinued = 'discontinued';

    public function getLabel(): string
    {
        return __('enums.stock_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InStock => 'success',
            self::OnOrder => 'warning',
            self::OutOfStock => 'danger',
            self::Discontinued => 'gray',
        };
    }

    /**
     * schema.org availability, used by the Product JSON-LD block.
     */
    public function schemaAvailability(): string
    {
        return match ($this) {
            self::InStock => 'https://schema.org/InStock',
            self::OnOrder => 'https://schema.org/PreOrder',
            self::OutOfStock => 'https://schema.org/OutOfStock',
            self::Discontinued => 'https://schema.org/Discontinued',
        };
    }
}
