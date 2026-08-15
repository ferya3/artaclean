<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.field.name'))->searchable(),
                TextColumn::make('slug')->label(__('admin.field.slug'))->badge()->copyable(),
                TextColumn::make('parent.name')->label(__('admin.field.parent'))->placeholder('—'),
                TextColumn::make('products_count')->counts('products')->label(__('admin.field.products'))->alignEnd(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
                IconColumn::make('show_in_menu')->label(__('admin.field.show_in_menu'))->boolean()->toggleable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
