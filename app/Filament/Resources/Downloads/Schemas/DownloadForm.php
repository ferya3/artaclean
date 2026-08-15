<?php

declare(strict_types=1);

namespace App\Filament\Resources\Downloads\Schemas;

use App\Enums\DownloadType;
use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DownloadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableFields::tabs('title', __('admin.field.title')),
            TranslatableFields::tabs('description', __('admin.field.description'), 'textarea', false),

            Grid::make(3)->schema([
                Select::make('type')->label(__('admin.field.type'))->options(DownloadType::class)->required(),
                Select::make('product_id')
                    ->label(__('admin.field.product'))
                    ->relationship('product', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable()
                    ->preload(),
                Select::make('language')
                    ->label(__('admin.field.language'))
                    ->options(['fa' => 'فارسی', 'en' => 'English'])
                    ->default('fa'),
            ]),

            FileUpload::make('file_path')
                ->label(__('admin.field.file'))
                ->directory('downloads')
                ->acceptedFileTypes(['application/pdf', 'application/zip', 'image/*'])
                ->maxSize(51200)
                ->required()
                ->storeFileNamesIn('original_name')
                // Size and mime feed the download list UI without re-statting
                // the object store on every page render.
                ->afterStateUpdated(function ($state, $set) {
                    if ($state && method_exists($state, 'getSize')) {
                        $set('file_size', $state->getSize());
                        $set('mime_type', $state->getMimeType());
                    }
                }),

            Grid::make(4)->schema([
                TextInput::make('file_size')->label(__('admin.field.file_size'))->numeric()->suffix('bytes'),
                TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                Toggle::make('is_public')->label(__('admin.field.is_public'))->default(true),
                Toggle::make('requires_lead')
                    ->label(__('admin.field.requires_lead'))
                    ->helperText(__('admin.help.requires_lead')),
            ]),
        ]);
    }
}
