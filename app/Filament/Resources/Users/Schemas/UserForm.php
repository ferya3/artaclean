<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('name')->label(__('admin.field.name'))->required(),
                TextInput::make('email')->label(__('admin.field.email'))->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->label(__('admin.field.phone'))->tel()->unique(ignoreRecord: true),
                TextInput::make('company')->label(__('admin.field.company')),
                TextInput::make('city')->label(__('admin.field.city')),
                Select::make('locale')
                    ->label(__('admin.field.language'))
                    ->options(['fa' => 'فارسی', 'en' => 'English'])
                    ->default('fa'),
            ]),

            TextInput::make('password')
                ->label(__('admin.field.password'))
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                // Leaving it blank on edit must not wipe the existing hash.
                ->dehydrated(fn (?string $state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create'),

            Grid::make(3)->schema([
                Select::make('roles')
                    ->label(__('admin.field.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->required(),
                Select::make('dealer_id')
                    ->label(__('admin.field.dealer'))
                    ->relationship('dealer', 'name')
                    ->searchable()
                    ->helperText(__('admin.help.dealer_link')),
                Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
            ]),
        ]);
    }
}
