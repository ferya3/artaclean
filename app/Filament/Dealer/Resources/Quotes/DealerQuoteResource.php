<?php

declare(strict_types=1);

namespace App\Filament\Dealer\Resources\Quotes;

use App\Enums\QuoteStatus;
use App\Filament\Dealer\Resources\Quotes\Pages\EditDealerQuote;
use App\Filament\Dealer\Resources\Quotes\Pages\ListDealerQuotes;
use App\Models\Quote;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DealerQuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    // Without an explicit slug the nested directory would produce
    // /dealer/leads/dealer-leads.
    protected static ?string $slug = 'quotes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    public static function getModelLabel(): string
    {
        return __('admin.model.quote');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model.quotes');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.request'))->columns(3)->schema([
                TextInput::make('reference')->label(__('admin.field.reference'))->disabled(),
                TextInput::make('quantity')->label(__('admin.field.quantity'))->disabled(),
                Textarea::make('customer_note')->label(__('admin.field.customer_note'))->disabled()->columnSpanFull()->rows(2),
            ]),

            Section::make(__('admin.section.response'))->columns(3)->schema([
                Select::make('status')->label(__('admin.field.status'))->options(QuoteStatus::class)->required(),
                TextInput::make('quoted_price')->label(__('admin.field.quoted_price'))->numeric()->suffix('ریال'),
                DatePicker::make('valid_until')->label(__('admin.field.valid_until')),
                Textarea::make('response_note')->label(__('admin.field.response_note'))->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label(__('admin.field.reference'))->badge(),
                TextColumn::make('lead.name')->label(__('admin.field.customer'))->searchable(),
                TextColumn::make('product.name')->label(__('admin.field.product'))->limit(28)->placeholder('—'),
                TextColumn::make('status')->label(__('admin.field.status'))->badge(),
                TextColumn::make('quoted_price')->label(__('admin.field.quoted_price'))->numeric()->alignEnd()->placeholder('—'),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('admin.field.status'))->options(QuoteStatus::class)->multiple(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealerQuotes::route('/'),
            'edit' => EditDealerQuote::route('/{record}/edit'),
        ];
    }
}
