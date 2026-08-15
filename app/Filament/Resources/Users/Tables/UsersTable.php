<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.field.name'))->searchable(),
                TextColumn::make('email')->label(__('admin.field.email'))->searchable()->copyable(),
                TextColumn::make('roles.name')->label(__('admin.field.roles'))->badge(),
                TextColumn::make('dealer.name')->label(__('admin.field.dealer'))->placeholder('—')->toggleable(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
                TextColumn::make('last_login_at')->label(__('admin.field.last_login'))->since()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('roles')->label(__('admin.field.roles'))->relationship('roles', 'name'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
