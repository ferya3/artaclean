<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.content'))->schema([
                TranslatableFields::tabs('name', __('admin.field.name')),

                Grid::make(2)->schema([
                    TextInput::make('slug')
                        ->label(__('admin.field.slug'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        // These slugs are the top-level SEO landing URLs
                        // (/industrial-vacuum-cleaner), so they stay English.
                        ->helperText(__('admin.help.category_slug')),
                    Select::make('parent_id')
                        ->label(__('admin.field.parent'))
                        ->relationship('parent', 'slug', fn ($query, $record) => $query->when($record, fn ($q) => $q->whereKeyNot($record->getKey())))
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->searchable()
                        ->preload(),
                ]),

                TranslatableFields::tabs('short_description', __('admin.field.short_description'), 'textarea', false),
                TranslatableFields::tabs('description', __('admin.field.description'), 'rich', false),
            ]),

            Section::make(__('admin.section.media'))->columns(3)->schema([
                FileUpload::make('image')->label(__('admin.field.image'))->image()->directory('categories'),
                FileUpload::make('banner')->label(__('admin.field.banner'))->image()->directory('categories/banners'),
                TextInput::make('icon')->label(__('admin.field.icon'))->helperText(__('admin.help.icon')),
            ]),

            Section::make(__('admin.section.settings'))->columns(3)->schema([
                TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
                Toggle::make('show_in_menu')->label(__('admin.field.show_in_menu'))->default(true),
            ]),

            Section::make(__('admin.section.seo'))->collapsed()->schema([
                TranslatableFields::tabs('seo_title', __('admin.field.seo_title'), 'text', false),
                TranslatableFields::tabs('seo_description', __('admin.field.seo_description'), 'textarea', false),
            ]),
        ]);
    }
}
