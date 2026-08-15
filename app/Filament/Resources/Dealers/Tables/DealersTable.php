<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dealers\Tables;

use App\Models\Dealer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DealersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.field.name'))->searchable()
                    ->description(fn ($record) => $record->company_name),
                TextColumn::make('province')->label(__('admin.field.province'))->badge(),
                TextColumn::make('city')->label(__('admin.field.city')),
                TextColumn::make('tier')
                    ->label(__('admin.field.tier'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('enums.dealer_tier.'.$state))
                    ->color(fn (string $state) => match ($state) {
                        'platinum' => 'info',
                        'gold' => 'warning',
                        'silver' => 'gray',
                        default => 'danger',
                    }),
                TextColumn::make('leads_count')->counts('leads')->label(__('admin.model.leads'))->alignEnd(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('province')
                    ->label(__('admin.field.province'))
                    ->options(fn () => Dealer::query()->whereNotNull('province')->distinct()->pluck('province', 'province')->all()),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
