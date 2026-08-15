<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Models\Lead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'crm';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.group.crm');
    }

    public static function getModelLabel(): string
    {
        return __('admin.model.lead');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model.leads');
    }

    /** New leads sitting in the queue, shown as a badge in the sidebar. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', LeadStatus::New)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'company', 'reference'];
    }
}
