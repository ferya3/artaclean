<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dealers;

use App\Filament\Resources\Dealers\Pages\CreateDealer;
use App\Filament\Resources\Dealers\Pages\EditDealer;
use App\Filament\Resources\Dealers\Pages\ListDealers;
use App\Filament\Resources\Dealers\Schemas\DealerForm;
use App\Filament\Resources\Dealers\Tables\DealersTable;
use App\Models\Dealer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'network';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.group.network');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model.dealer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model.dealers');
    }

    public static function form(Schema $schema): Schema
    {
        return DealerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealers::route('/'),
            'create' => CreateDealer::route('/create'),
            'edit' => EditDealer::route('/{record}/edit'),
        ];
    }
}
