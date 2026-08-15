<?php

declare(strict_types=1);

namespace App\Filament\Resources\Environments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnvironmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.field.name'))->searchable(),
                TextColumn::make('slug')->label(__('admin.field.slug'))->badge(),
                TextColumn::make('products_count')->counts('products')->label(__('admin.field.products'))->alignEnd(),
                TextColumn::make('typical_area_max')->label(__('admin.field.area_max'))->suffix(' m²')->alignEnd()->toggleable(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
