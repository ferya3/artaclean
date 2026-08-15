<?php

declare(strict_types=1);

namespace App\Filament\Resources\Environments;

use App\Filament\Resources\Environments\Pages\CreateEnvironment;
use App\Filament\Resources\Environments\Pages\EditEnvironment;
use App\Filament\Resources\Environments\Pages\ListEnvironments;
use App\Filament\Resources\Environments\Schemas\EnvironmentForm;
use App\Filament\Resources\Environments\Tables\EnvironmentsTable;
use App\Models\Environment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EnvironmentResource extends Resource
{
    protected static ?string $model = Environment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'catalog';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.group.catalog');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model.environment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model.environments');
    }

    public static function form(Schema $schema): Schema
    {
        return EnvironmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnvironmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnvironments::route('/'),
            'create' => CreateEnvironment::route('/create'),
            'edit' => EditEnvironment::route('/{record}/edit'),
        ];
    }
}
