<?php

declare(strict_types=1);

namespace App\Filament\Resources\Environments\Schemas;

use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnvironmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.content'))->schema([
                TranslatableFields::tabs('name', __('admin.field.name')),

                TextInput::make('slug')->label(__('admin.field.slug'))->required()->unique(ignoreRecord: true),

                TranslatableFields::tabs('short_description', __('admin.field.short_description'), 'textarea', false),
                TranslatableFields::tabs('description', __('admin.field.description'), 'rich', false),
                TranslatableFields::tabs('challenges', __('admin.field.challenges'), 'tags', false),
            ]),

            Section::make(__('admin.section.sizing'))
                ->description(__('admin.section.sizing_hint'))
                ->columns(4)
                ->schema([
                    TextInput::make('typical_area_min')->label(__('admin.field.area_min'))->numeric()->suffix('m²'),
                    TextInput::make('typical_area_max')->label(__('admin.field.area_max'))->numeric()->suffix('m²'),
                    TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                    Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
                ]),

            Grid::make(2)->schema([
                FileUpload::make('image')->label(__('admin.field.image'))->image()->directory('environments'),
                TextInput::make('icon')->label(__('admin.field.icon')),
            ]),

            Section::make(__('admin.section.seo'))->collapsed()->schema([
                TranslatableFields::tabs('seo_title', __('admin.field.seo_title'), 'text', false),
                TranslatableFields::tabs('seo_description', __('admin.field.seo_description'), 'textarea', false),
            ]),
        ]);
    }
}
