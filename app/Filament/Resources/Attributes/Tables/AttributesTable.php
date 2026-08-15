<?php

declare(strict_types=1);

namespace App\Filament\Resources\Attributes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.field.name'))->searchable(),
                TextColumn::make('slug')->label(__('admin.field.slug'))->badge(),
                TextColumn::make('type')
                    ->label(__('admin.field.type'))
                    ->formatStateUsing(fn (string $state) => __('enums.attribute_type.'.$state)),
                TextColumn::make('values_count')->counts('values')->label(__('admin.section.values'))->alignEnd(),
                IconColumn::make('is_filterable')->label(__('admin.field.is_filterable'))->boolean(),
                IconColumn::make('is_comparable')->label(__('admin.field.is_comparable'))->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
