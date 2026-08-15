<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestLeads extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function getTableHeading(): string
    {
        return __('admin.widget.latest_leads');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()->with(['product', 'assignee'])->latest('id')->limit(10)
            )
            ->columns([
                TextColumn::make('name')->label(__('admin.field.name'))
                    ->description(fn (Lead $record) => $record->phone),
                TextColumn::make('product.name')->label(__('admin.field.product'))->limit(30)->placeholder('—'),
                TextColumn::make('source')->label(__('admin.field.source'))->badge(),
                TextColumn::make('score')
                    ->label(__('admin.field.score'))
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn (int $state) => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'gray')),
                TextColumn::make('status')->label(__('admin.field.status'))->badge(),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->since(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label(__('admin.action.open'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Lead $record) => LeadResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
