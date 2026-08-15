<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brands\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->label('')->size(40),
                TextColumn::make('name')->label(__('admin.field.name'))->searchable(),
                TextColumn::make('slug')->label(__('admin.field.slug'))->badge(),
                TextColumn::make('country')->label(__('admin.field.country'))->toggleable(),
                TextColumn::make('products_count')->counts('products')->label(__('admin.field.products'))->alignEnd(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
