<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                Section::make(__('admin.section.contact'))
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label(__('admin.field.name'))->required(),
                        TextInput::make('phone')->label(__('admin.field.phone'))->tel()->required(),
                        TextInput::make('email')->label(__('admin.field.email'))->email(),
                        TextInput::make('company')->label(__('admin.field.company')),
                        TextInput::make('city')->label(__('admin.field.city')),
                        Select::make('business_type')
                            ->label(__('admin.field.business_type'))
                            ->options(fn () => __('business_types')),
                        Textarea::make('message')->label(__('admin.field.message'))->rows(3)->columnSpanFull(),
                        Textarea::make('internal_notes')
                            ->label(__('admin.field.internal_notes'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('admin.help.internal_notes')),
                    ]),

                Section::make(__('admin.section.pipeline'))
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->label(__('admin.field.status'))
                            ->options(LeadStatus::class)
                            ->default(LeadStatus::New)
                            ->required()
                            ->live(),
                        Select::make('source')
                            ->label(__('admin.field.source'))
                            ->options(LeadSource::class)
                            ->required(),
                        Select::make('assigned_to')
                            ->label(__('admin.field.assigned_to'))
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('dealer_id')
                            ->label(__('admin.field.dealer'))
                            ->relationship('dealer', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('admin.help.assign_dealer')),
                        TextInput::make('score')
                            ->label(__('admin.field.score'))
                            ->numeric()
                            ->suffix('/100')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('estimated_value')->label(__('admin.field.estimated_value'))->numeric()->suffix('ریال'),
                        DateTimePicker::make('last_contacted_at')->label(__('admin.field.last_contacted_at')),
                    ]),
            ]),

            Section::make(__('admin.section.attribution'))
                ->collapsed()
                ->columns(3)
                ->schema([
                    Select::make('product_id')
                        ->label(__('admin.field.product'))
                        ->relationship('product', 'slug')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->searchable()
                        ->preload(),
                    Select::make('environment_id')
                        ->label(__('admin.field.environment'))
                        ->relationship('environment', 'slug')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->preload(),
                    TextInput::make('landing_page')->label(__('admin.field.landing_page'))->disabled(),
                    TextInput::make('utm_source')->label('utm_source')->disabled(),
                    TextInput::make('utm_medium')->label('utm_medium')->disabled(),
                    TextInput::make('utm_campaign')->label('utm_campaign')->disabled(),
                ]),
        ]);
    }
}
