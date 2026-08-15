<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function getLabel(): string
    {
        return __('enums.lead_status.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Contacted => 'primary',
            self::Qualified, self::Proposal, self::Negotiation => 'warning',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::New => 'heroicon-o-sparkles',
            self::Contacted => 'heroicon-o-phone',
            self::Qualified => 'heroicon-o-check-badge',
            self::Proposal => 'heroicon-o-document-text',
            self::Negotiation => 'heroicon-o-chat-bubble-left-right',
            self::Won => 'heroicon-o-trophy',
            self::Lost => 'heroicon-o-x-circle',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Won, self::Lost], true);
    }
}
