<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Tables;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label(__('admin.field.reference'))->badge()->copyable(),
                TextColumn::make('lead.name')->label(__('admin.field.customer'))->searchable()
                    ->description(fn (Quote $record) => $record->lead?->phone),
                TextColumn::make('product.name')->label(__('admin.field.product'))->limit(30)->placeholder('—'),
                TextColumn::make('type')
                    ->label(__('admin.field.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('enums.quote_type.'.$state)),
                TextColumn::make('quantity')->label(__('admin.field.quantity'))->alignEnd(),
                TextColumn::make('status')->label(__('admin.field.status'))->badge(),
                TextColumn::make('quoted_price')
                    ->label(__('admin.field.quoted_price'))
                    ->numeric()
                    ->placeholder('—')
                    ->alignEnd(),
                TextColumn::make('valid_until')
                    ->label(__('admin.field.valid_until'))
                    ->date()
                    ->color(fn (Quote $record) => $record->isExpired() ? 'danger' : null),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('admin.field.status'))->options(QuoteStatus::class)->multiple(),
                Filter::make('awaiting')
                    ->label(__('admin.filter.awaiting_response'))
                    ->query(fn ($query) => $query->awaitingResponse())
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('markSent')
                    ->label(__('admin.action.mark_sent'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record) => in_array($record->status, [QuoteStatus::Pending, QuoteStatus::InReview], true))
                    ->action(function (Quote $record) {
                        $record->update([
                            'status' => QuoteStatus::Sent,
                            'responded_by' => auth()->id(),
                            'responded_at' => now(),
                        ]);

                        $record->lead?->activities()->create([
                            'user_id' => auth()->id(),
                            'type' => 'quote_sent',
                            'body' => __('crm.quote_sent', ['reference' => $record->reference]),
                        ]);

                        Notification::make()->title(__('admin.notify.quote_sent'))->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }
}
