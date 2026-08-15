<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeadSource: string implements HasLabel
{
    case Quote = 'quote';
    case Contact = 'contact';
    case Calculator = 'calculator';
    case Catalog = 'catalog';
    case Whatsapp = 'whatsapp';
    case Rental = 'rental';
    case Phone = 'phone';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('enums.lead_source.'.$this->value);
    }

    /**
     * A first-pass lead score. A rental enquiry or a price request from a
     * product page is worth more attention than an anonymous catalog download.
     */
    public function baseScore(): int
    {
        return match ($this) {
            self::Quote => 40,
            self::Rental => 35,
            self::Calculator => 30,
            self::Phone, self::Whatsapp => 25,
            self::Catalog => 15,
            self::Contact => 10,
            self::Other => 5,
        };
    }
}
