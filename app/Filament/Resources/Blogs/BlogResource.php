<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blogs;

use App\Filament\Resources\Blogs\Pages\CreateBlog;
use App\Filament\Resources\Blogs\Pages\EditBlog;
use App\Filament\Resources\Blogs\Pages\ListBlogs;
use App\Filament\Resources\Blogs\Schemas\BlogForm;
use App\Filament\Resources\Blogs\Tables\BlogsTable;
use App\Models\Blog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'content';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.group.content');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model.article');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model.articles');
    }

    public static function form(Schema $schema): Schema
    {
        return BlogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogs::route('/'),
            'create' => CreateBlog::route('/create'),
            'edit' => EditBlog::route('/{record}/edit'),
        ];
    }
}
