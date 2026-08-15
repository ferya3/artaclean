<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dealers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.identity'))->columns(3)->schema([
                TextInput::make('name')->label(__('admin.field.name'))->required(),
                TextInput::make('slug')->label(__('admin.field.slug'))->required()->unique(ignoreRecord: true),
                TextInput::make('company_name')->label(__('admin.field.company')),
                Select::make('user_id')
                    ->label(__('admin.field.panel_user'))
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText(__('admin.help.dealer_user')),
                Select::make('tier')
                    ->label(__('admin.field.tier'))
                    ->options([
                        'bronze' => __('enums.dealer_tier.bronze'),
                        'silver' => __('enums.dealer_tier.silver'),
                        'gold' => __('enums.dealer_tier.gold'),
                        'platinum' => __('enums.dealer_tier.platinum'),
                    ])
                    ->default('bronze'),
                TextInput::make('commission_rate')->label(__('admin.field.commission_rate'))->numeric()->suffix('%'),
            ]),

            Section::make(__('admin.section.contact'))->columns(3)->schema([
                TextInput::make('province')->label(__('admin.field.province')),
                TextInput::make('city')->label(__('admin.field.city')),
                TextInput::make('phone')->label(__('admin.field.phone'))->tel(),
                TextInput::make('email')->label(__('admin.field.email'))->email(),
                TextInput::make('latitude')->label(__('admin.field.latitude'))->numeric(),
                TextInput::make('longitude')->label(__('admin.field.longitude'))->numeric(),
                Textarea::make('address')->label(__('admin.field.address'))->columnSpanFull()->rows(2),
                Textarea::make('description')->label(__('admin.field.description'))->columnSpanFull()->rows(3),
            ]),

            Grid::make(2)->schema([
                FileUpload::make('logo')->label(__('admin.field.logo'))->image()->directory('dealers'),
                Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
            ]),
        ]);
    }
}
