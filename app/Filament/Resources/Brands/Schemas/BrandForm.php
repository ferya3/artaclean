<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brands\Schemas;

use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableFields::tabs('name', __('admin.field.name')),

            Grid::make(3)->schema([
                TextInput::make('slug')->label(__('admin.field.slug'))->required()->unique(ignoreRecord: true),
                TextInput::make('country')->label(__('admin.field.country')),
                TextInput::make('website')->label(__('admin.field.website'))->url(),
            ]),

            TranslatableFields::tabs('description', __('admin.field.description'), 'textarea', false),

            Grid::make(3)->schema([
                FileUpload::make('logo')->label(__('admin.field.logo'))->image()->directory('brands'),
                TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
            ]),
        ]);
    }
}
