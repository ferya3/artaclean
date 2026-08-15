<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Enums\PowerSource;
use App\Enums\StockStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')->label('')->square()->size(48),

                TextColumn::make('name')
                    ->label(__('admin.field.name'))
                    ->searchable(query: fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                    ->description(fn ($record) => $record->sku)
                    ->wrap(),

                TextColumn::make('category.name')->label(__('admin.field.category'))->badge()->sortable(),
                TextColumn::make('brand.name')->label(__('admin.field.brand'))->toggleable(),

                TextColumn::make('productivity_sqm_h')
                    ->label(__('specs.productivity_sqm_h'))
                    ->suffix(' m²/h')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('stock_status')->label(__('admin.field.stock_status'))->badge(),

                TextColumn::make('quotes_count')
                    ->label(__('admin.field.quotes_count'))
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
                IconColumn::make('is_featured')->label(__('admin.field.is_featured'))->boolean()->toggleable(),
                IconColumn::make('is_rentable')->label(__('admin.field.is_rentable'))->boolean()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('admin.field.category'))
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('brand')
                    ->label(__('admin.field.brand'))
                    ->relationship('brand', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name),
                SelectFilter::make('power_source')->label(__('specs.power_source'))->options(PowerSource::class),
                SelectFilter::make('stock_status')->label(__('admin.field.stock_status'))->options(StockStatus::class),
                TernaryFilter::make('is_active')->label(__('admin.field.is_active')),
                TernaryFilter::make('is_rentable')->label(__('admin.field.is_rentable')),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->excludeAttributes(['slug', 'sku', 'views_count', 'quotes_count'])
                    // A near-identical variant is the usual reason to copy a
                    // product, so the clone starts hidden until it is edited.
                    ->beforeReplicaSaved(function ($replica, $record) {
                        $replica->slug = $record->slug.'-copy-'.uniqid();
                        $replica->is_active = false;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
