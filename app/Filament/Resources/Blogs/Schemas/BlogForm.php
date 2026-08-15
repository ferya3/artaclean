<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blogs\Schemas;

use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.content'))->schema([
                TranslatableFields::tabs('title', __('admin.field.title')),
                TextInput::make('slug')->label(__('admin.field.slug'))->required()->unique(ignoreRecord: true),
                TranslatableFields::tabs('excerpt', __('admin.field.excerpt'), 'textarea', false),
                TranslatableFields::tabs('body', __('admin.field.body'), 'rich'),
            ]),

            Section::make(__('admin.section.settings'))->columns(3)->schema([
                Select::make('category_id')
                    ->label(__('admin.field.category'))
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable()
                    ->preload(),
                Select::make('author_id')->label(__('admin.field.author'))->relationship('author', 'name')->searchable(),
                TextInput::make('reading_minutes')->label(__('admin.field.reading_minutes'))->numeric()->default(3),
                FileUpload::make('cover_image')->label(__('admin.field.cover_image'))->image()->directory('blog'),
                DateTimePicker::make('published_at')->label(__('admin.field.published_at'))->default(now()),
                Toggle::make('is_published')->label(__('admin.field.is_published')),

                // Linking an article to machines gives the product page its
                // "مقاله مرتبط" block and spreads internal link equity.
                Select::make('products')
                    ->label(__('admin.field.related_products'))
                    ->relationship('products', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]),

            Section::make(__('admin.section.seo'))->collapsed()->schema([
                TranslatableFields::tabs('seo_title', __('admin.field.seo_title'), 'text', false),
                TranslatableFields::tabs('seo_description', __('admin.field.seo_description'), 'textarea', false),
            ]),
        ]);
    }
}
