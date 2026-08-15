<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuoteStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __('enums.quote_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::InReview => 'primary',
            self::Sent => 'warning',
            self::Accepted => 'success',
            self::Rejected, self::Expired => 'danger',
        };
    }
}
