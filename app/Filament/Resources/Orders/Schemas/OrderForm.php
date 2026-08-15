<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.customer'))->columns(3)->schema([
                TextInput::make('customer_name')->label(__('admin.field.name'))->required(),
                TextInput::make('customer_phone')->label(__('admin.field.phone'))->tel()->required(),
                TextInput::make('customer_email')->label(__('admin.field.email'))->email(),
                Select::make('dealer_id')->label(__('admin.field.dealer'))->relationship('dealer', 'name')->searchable(),
                Select::make('status')->label(__('admin.field.status'))->options(OrderStatus::class)->required(),
                Select::make('payment_status')
                    ->label(__('admin.field.payment_status'))
                    ->options([
                        'unpaid' => __('enums.payment_status.unpaid'),
                        'partial' => __('enums.payment_status.partial'),
                        'paid' => __('enums.payment_status.paid'),
                        'refunded' => __('enums.payment_status.refunded'),
                    ])
                    ->required(),
            ]),

            Section::make(__('admin.section.items'))->schema([
                Repeater::make('items')
                    ->relationship()
                    ->hiddenLabel()
                    ->columns(4)
                    ->schema([
                        Select::make('product_id')
                            ->label(__('admin.field.product'))
                            ->relationship('product', 'slug')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->live()
                            // Snapshot name and price so the order stays
                            // readable after the catalog moves on.
                            ->afterStateUpdated(function ($state, $set) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set('name', (string) $product->name);
                                    $set('sku', $product->sku);
                                    $set('unit_price', $product->price);
                                }
                            }),
                        TextInput::make('name')->label(__('admin.field.name'))->required(),
                        TextInput::make('unit_price')->label(__('admin.field.unit_price'))->numeric()->required(),
                        TextInput::make('quantity')->label(__('admin.field.quantity'))->numeric()->default(1)->required(),
                    ]),
            ]),

            Section::make(__('admin.section.totals'))->columns(4)->schema([
                TextInput::make('discount')->label(__('admin.field.discount'))->numeric()->default(0),
                TextInput::make('tax')->label(__('admin.field.tax'))->numeric()->default(0),
                TextInput::make('shipping')->label(__('admin.field.shipping'))->numeric()->default(0),
                TextInput::make('total')->label(__('admin.field.total'))->numeric()->disabled()->dehydrated(false),
                Textarea::make('notes')->label(__('admin.field.notes'))->columnSpanFull()->rows(2),
            ]),
        ]);
    }
}
