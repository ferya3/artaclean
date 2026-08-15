<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $newLeads = Lead::where('status', LeadStatus::New)->count();
        $thisWeek = Lead::where('created_at', '>=', now()->subDays(7))->count();
        $lastWeek = Lead::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $won = Lead::where('status', LeadStatus::Won)->count();
        $closed = Lead::whereIn('status', [LeadStatus::Won, LeadStatus::Lost])->count();
        $winRate = $closed > 0 ? round($won / $closed * 100) : 0;

        return [
            Stat::make(__('admin.stat.new_leads'), (string) $newLeads)
                ->description(__('admin.stat.awaiting_first_call'))
                ->descriptionIcon('heroicon-m-phone')
                ->color($newLeads > 10 ? 'danger' : 'success'),

            Stat::make(__('admin.stat.leads_this_week'), (string) $thisWeek)
                ->description($this->trendLabel($thisWeek, $lastWeek))
                ->descriptionIcon($thisWeek >= $lastWeek ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($thisWeek >= $lastWeek ? 'success' : 'warning')
                ->chart($this->dailyLeadCounts()),

            Stat::make(__('admin.stat.open_quotes'), (string) Quote::query()->awaitingResponse()->count())
                ->description(__('admin.stat.needs_pricing'))
                ->descriptionIcon('heroicon-m-document-currency-dollar')
                ->color('warning'),

            Stat::make(__('admin.stat.win_rate'), $winRate.'%')
                ->description(__('admin.stat.of_closed_leads', ['count' => $closed]))
                ->descriptionIcon('heroicon-m-trophy')
                ->color($winRate >= 30 ? 'success' : 'gray'),

            Stat::make(__('admin.stat.quoted_products'), (string) Product::where('quotes_count', '>', 0)->count())
                ->description(__('admin.stat.of_total', ['count' => Product::count()]))
                ->descriptionIcon('heroicon-m-cube'),

            Stat::make(__('admin.stat.accepted_quotes'), (string) Quote::where('status', QuoteStatus::Accepted)->count())
                ->description(__('admin.stat.all_time'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }

    /** Seven-day sparkline for the leads stat. */
    private function dailyLeadCounts(): array
    {
        $counts = Lead::query()
            ->where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Lead $lead) => $lead->created_at->toDateString());

        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => $counts->get(now()->subDays($daysAgo)->toDateString(), 0))
            ->all();
    }

    private function trendLabel(int $current, int $previous): string
    {
        if ($previous === 0) {
            return __('admin.stat.no_prior_data');
        }

        $delta = round(($current - $previous) / $previous * 100);

        return __('admin.stat.vs_last_week', ['delta' => ($delta >= 0 ? '+' : '').$delta.'%']);
    }
}
