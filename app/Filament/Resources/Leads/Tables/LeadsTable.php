<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label(__('admin.field.reference'))->badge()->copyable()->toggleable(),
                TextColumn::make('name')
                    ->label(__('admin.field.name'))
                    ->searchable()
                    ->description(fn (Lead $record) => $record->company),
                TextColumn::make('phone')->label(__('admin.field.phone'))->searchable()->copyable(),
                TextColumn::make('city')->label(__('admin.field.city'))->toggleable(),
                TextColumn::make('product.name')->label(__('admin.field.product'))->limit(28)->placeholder('—'),
                TextColumn::make('source')->label(__('admin.field.source'))->badge(),
                TextColumn::make('status')->label(__('admin.field.status'))->badge(),
                TextColumn::make('score')
                    ->label(__('admin.field.score'))
                    ->alignEnd()
                    ->sortable()
                    ->color(fn (int $state) => match (true) {
                        $state >= 70 => 'success',
                        $state >= 40 => 'warning',
                        default => 'gray',
                    })
                    ->weight('bold'),
                TextColumn::make('assignee.name')->label(__('admin.field.assigned_to'))->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('admin.field.status'))->options(LeadStatus::class)->multiple(),
                SelectFilter::make('source')->label(__('admin.field.source'))->options(LeadSource::class)->multiple(),
                SelectFilter::make('assigned_to')
                    ->label(__('admin.field.assigned_to'))
                    ->relationship('assignee', 'name'),

                // The queue that actually matters: open, never contacted, older
                // than a day.
                Filter::make('stale')
                    ->label(__('admin.filter.stale_leads'))
                    ->query(fn ($query) => $query->stale())
                    ->toggle(),

                Filter::make('unassigned')
                    ->label(__('admin.filter.unassigned'))
                    ->query(fn ($query) => $query->unassigned())
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('markContacted')
                    ->label(__('admin.action.mark_contacted'))
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->visible(fn (Lead $record) => $record->status === LeadStatus::New)
                    ->action(function (Lead $record) {
                        $record->update([
                            'status' => LeadStatus::Contacted,
                            'last_contacted_at' => now(),
                        ]);

                        $record->activities()->create([
                            'user_id' => auth()->id(),
                            'type' => 'status_change',
                            'body' => __('crm.marked_contacted'),
                        ]);

                        Notification::make()->title(__('admin.notify.lead_updated'))->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign')
                        ->label(__('admin.action.assign_bulk'))
                        ->icon('heroicon-o-user-plus')
                        ->schema([
                            Select::make('assigned_to')
                                ->label(__('admin.field.assigned_to'))
                                ->relationship('assignee', 'name')
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update(['assigned_to' => $data['assigned_to']]);

                            Notification::make()
                                ->title(__('admin.notify.leads_assigned', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }
}
