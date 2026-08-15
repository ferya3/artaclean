<?php

declare(strict_types=1);

namespace App\Filament\Dealer\Resources\Leads;

use App\Enums\LeadStatus;
use App\Filament\Dealer\Resources\Leads\Pages\EditDealerLead;
use App\Filament\Dealer\Resources\Leads\Pages\ListDealerLeads;
use App\Models\Lead;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DealerLeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    // Without an explicit slug the nested directory would produce
    // /dealer/leads/dealer-leads.
    protected static ?string $slug = 'leads';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function getModelLabel(): string
    {
        return __('admin.model.lead');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model.leads');
    }

    /** A dealer works its own pipeline; leads are routed in by head office. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.section.contact'))->columns(2)->schema([
                TextInput::make('name')->label(__('admin.field.name'))->disabled(),
                TextInput::make('phone')->label(__('admin.field.phone'))->disabled(),
                TextInput::make('company')->label(__('admin.field.company'))->disabled(),
                TextInput::make('city')->label(__('admin.field.city'))->disabled(),
                Textarea::make('message')->label(__('admin.field.message'))->disabled()->columnSpanFull()->rows(3),
            ]),

            Section::make(__('admin.section.pipeline'))->columns(2)->schema([
                Select::make('status')->label(__('admin.field.status'))->options(LeadStatus::class)->required(),
                DateTimePicker::make('last_contacted_at')->label(__('admin.field.last_contacted_at')),
                Textarea::make('internal_notes')->label(__('admin.field.internal_notes'))->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label(__('admin.field.reference'))->badge(),
                TextColumn::make('name')->label(__('admin.field.name'))->searchable()
                    ->description(fn (Lead $record) => $record->phone),
                TextColumn::make('city')->label(__('admin.field.city')),
                TextColumn::make('product.name')->label(__('admin.field.product'))->limit(28)->placeholder('—'),
                TextColumn::make('status')->label(__('admin.field.status'))->badge(),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('admin.field.status'))->options(LeadStatus::class)->multiple(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealerLeads::route('/'),
            'edit' => EditDealerLead::route('/{record}/edit'),
        ];
    }
}
