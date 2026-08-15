<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use App\Enums\RentalPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.request'))->columns(3)->schema([
                Select::make('lead_id')
                    ->label(__('admin.field.lead'))
                    ->relationship('lead', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' — '.$record->phone)
                    ->searchable()
                    ->required(),
                Select::make('product_id')
                    ->label(__('admin.field.product'))
                    ->relationship('product', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')->label(__('admin.field.quantity'))->numeric()->default(1)->required(),

                Select::make('type')
                    ->label(__('admin.field.type'))
                    ->options([
                        'purchase' => __('enums.quote_type.purchase'),
                        'rental' => __('enums.quote_type.rental'),
                    ])
                    ->default('purchase')
                    ->required()
                    ->live(),
                Select::make('rental_period')
                    ->label(__('admin.field.period'))
                    ->options(RentalPeriod::class)
                    ->visible(fn ($get) => $get('type') === 'rental'),
                TextInput::make('rental_duration')
                    ->label(__('admin.field.duration'))
                    ->numeric()
                    ->visible(fn ($get) => $get('type') === 'rental'),

                Textarea::make('customer_note')->label(__('admin.field.customer_note'))->rows(2)->columnSpanFull(),
            ]),

            Section::make(__('admin.section.response'))->columns(3)->schema([
                Select::make('status')->label(__('admin.field.status'))->options(QuoteStatus::class)->required(),
                TextInput::make('quoted_price')->label(__('admin.field.quoted_price'))->numeric()->suffix('ریال'),
                TextInput::make('discount')->label(__('admin.field.discount'))->numeric()->suffix('ریال')->default(0),
                DatePicker::make('valid_until')
                    ->label(__('admin.field.valid_until'))
                    ->default(now()->addDays(14))
                    ->helperText(__('admin.help.valid_until')),
                Textarea::make('response_note')->label(__('admin.field.response_note'))->rows(3)->columnSpan(2),
            ]),
        ]);
    }
}
