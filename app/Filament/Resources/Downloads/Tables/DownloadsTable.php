<?php

declare(strict_types=1);

namespace App\Filament\Resources\Downloads\Tables;

use App\Enums\DownloadType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DownloadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label(__('admin.field.title'))->searchable()->wrap(),
                TextColumn::make('type')->label(__('admin.field.type'))->badge(),
                TextColumn::make('product.name')->label(__('admin.field.product'))->placeholder('—')->limit(30),
                TextColumn::make('language')->label(__('admin.field.language'))->badge(),
                TextColumn::make('download_count')->label(__('admin.field.downloads'))->alignEnd()->sortable(),
                IconColumn::make('requires_lead')->label(__('admin.field.requires_lead'))->boolean()->toggleable(),
                IconColumn::make('is_public')->label(__('admin.field.is_public'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')->label(__('admin.field.type'))->options(DownloadType::class),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
    }
}
