<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')->label('')->square()->size(44),
                TextColumn::make('title')->label(__('admin.field.title'))->searchable()->wrap(),
                TextColumn::make('category.name')->label(__('admin.field.category'))->badge()->placeholder('—'),
                TextColumn::make('author.name')->label(__('admin.field.author'))->placeholder('—')->toggleable(),
                TextColumn::make('views_count')->label(__('admin.field.views'))->alignEnd()->sortable(),
                IconColumn::make('is_published')->label(__('admin.field.is_published'))->boolean(),
                TextColumn::make('published_at')->label(__('admin.field.published_at'))->date()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label(__('admin.field.is_published')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('published_at', 'desc');
    }
}
