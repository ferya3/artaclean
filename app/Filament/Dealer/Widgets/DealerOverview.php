<?php

declare(strict_types=1);

namespace App\Filament\Dealer\Widgets;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DealerOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // The dealer global scope is already applied by the panel middleware,
        // so these counts are automatically confined to this dealer.
        return [
            Stat::make(__('admin.stat.new_leads'), (string) Lead::where('status', LeadStatus::New)->count())
                ->descriptionIcon('heroicon-m-phone')
                ->color('danger'),

            Stat::make(__('admin.stat.open_quotes'), (string) Quote::query()->awaitingResponse()->count())
                ->descriptionIcon('heroicon-m-document-currency-dollar')
                ->color('warning'),

            Stat::make(__('admin.stat.won'), (string) Lead::where('status', LeadStatus::Won)->count())
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
        ];
    }
}
