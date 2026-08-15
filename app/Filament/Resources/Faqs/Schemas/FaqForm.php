<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\TranslatableFields;
use App\Models\Category;
use App\Models\Environment;
use App\Models\Product;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableFields::tabs('question', __('admin.field.question')),
            TranslatableFields::tabs('answer', __('admin.field.answer'), 'rich'),

            // Leaving the owner empty publishes the entry on the site-wide FAQ.
            MorphToSelect::make('faqable')
                ->label(__('admin.field.attached_to'))
                ->types([
                    MorphToSelect\Type::make(Product::class)->titleAttribute('slug')->label(__('admin.model.product')),
                    MorphToSelect\Type::make(Category::class)->titleAttribute('slug')->label(__('admin.model.category')),
                    MorphToSelect\Type::make(Environment::class)->titleAttribute('slug')->label(__('admin.model.environment')),
                ])
                ->searchable()
                ->columnSpanFull(),

            Grid::make(3)->schema([
                TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
                Toggle::make('in_schema')
                    ->label(__('admin.field.in_schema'))
                    ->default(true)
                    ->helperText(__('admin.help.in_schema')),
            ]),
        ]);
    }
}
