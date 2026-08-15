<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label(__('admin.field.reference'))->badge()->copyable(),
                TextColumn::make('customer_name')->label(__('admin.field.customer'))->searchable()
                    ->description(fn ($record) => $record->customer_phone),
                TextColumn::make('dealer.name')->label(__('admin.field.dealer'))->placeholder('—')->toggleable(),
                TextColumn::make('items_count')->counts('items')->label(__('admin.section.items'))->alignEnd(),
                TextColumn::make('total')->label(__('admin.field.total'))->numeric()->alignEnd()->sortable(),
                TextColumn::make('status')->label(__('admin.field.status'))->badge(),
                TextColumn::make('payment_status')
                    ->label(__('admin.field.payment_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('enums.payment_status.'.$state))
                    ->color(fn (string $state) => $state === 'paid' ? 'success' : 'warning'),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('admin.field.status'))->options(OrderStatus::class)->multiple(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }
}
