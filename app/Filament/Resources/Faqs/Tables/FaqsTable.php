<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')->label(__('admin.field.question'))->searchable()->wrap()->limit(80),
                TextColumn::make('faqable_type')
                    ->label(__('admin.field.attached_to'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : __('admin.value.site_wide')),
                IconColumn::make('in_schema')->label(__('admin.field.in_schema'))->boolean(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
