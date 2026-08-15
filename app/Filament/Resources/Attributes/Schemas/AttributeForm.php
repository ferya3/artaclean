<?php

declare(strict_types=1);

namespace App\Filament\Resources\Attributes\Schemas;

use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableFields::tabs('name', __('admin.field.name')),

            Grid::make(4)->schema([
                TextInput::make('slug')->label(__('admin.field.slug'))->required()->unique(ignoreRecord: true),
                Select::make('type')
                    ->label(__('admin.field.type'))
                    ->options([
                        'select' => __('enums.attribute_type.select'),
                        'multiselect' => __('enums.attribute_type.multiselect'),
                        'number' => __('enums.attribute_type.number'),
                        'boolean' => __('enums.attribute_type.boolean'),
                    ])
                    ->default('select')
                    ->required(),
                TextInput::make('unit')->label(__('admin.field.unit')),
                TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
            ]),

            Grid::make(2)->schema([
                Toggle::make('is_filterable')->label(__('admin.field.is_filterable'))->default(true),
                Toggle::make('is_comparable')->label(__('admin.field.is_comparable'))->default(true),
            ]),

            // Restricting an attribute to categories keeps the sidebar honest:
            // a steam temperature facet has no business on a spare-parts page.
            Select::make('categories')
                ->label(__('admin.field.categories'))
                ->relationship('categories', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                ->multiple()
                ->preload()
                ->helperText(__('admin.help.attribute_categories')),

            Section::make(__('admin.section.values'))->schema([
                Repeater::make('values')
                    ->relationship()
                    ->hiddenLabel()
                    ->columns(3)
                    ->reorderable()
                    ->schema([
                        TextInput::make('slug')->label(__('admin.field.slug'))->required(),
                        TextInput::make('value.fa')->label(__('admin.field.value_fa'))->required(),
                        TextInput::make('value.en')->label(__('admin.field.value_en')),
                    ]),
            ]),
        ]);
    }
}
