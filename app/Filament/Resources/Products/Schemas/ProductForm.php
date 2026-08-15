<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\OperatorType;
use App\Enums\PowerSource;
use App\Enums\RentalPeriod;
use App\Enums\StockStatus;
use App\Filament\Support\TranslatableFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('product')->columnSpanFull()->tabs([

                Tabs\Tab::make(__('admin.tab.content'))->schema([
                    TranslatableFields::tabs('name', __('admin.field.name')),

                    Grid::make(3)->schema([
                        TextInput::make('slug')
                            ->label(__('admin.field.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText(__('admin.help.slug')),
                        TextInput::make('sku')->label('SKU')->unique(ignoreRecord: true),
                        TextInput::make('model_code')->label(__('admin.field.model_code')),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('category_id')
                            ->label(__('admin.field.category'))
                            ->relationship('category', 'slug')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('brand_id')
                            ->label(__('admin.field.brand'))
                            ->relationship('brand', 'slug')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable()
                            ->preload(),
                    ]),

                    TranslatableFields::tabs('short_description', __('admin.field.short_description'), 'textarea', false),
                    TranslatableFields::tabs('description', __('admin.field.description'), 'rich', false),
                    TranslatableFields::tabs('highlights', __('admin.field.highlights'), 'tags', false),
                ]),

                Tabs\Tab::make(__('admin.tab.specs'))->schema([
                    Section::make(__('admin.section.core_specs'))
                        ->description(__('admin.section.core_specs_hint'))
                        ->columns(3)
                        ->schema([
                            Select::make('power_source')
                                ->label(__('specs.power_source'))
                                ->options(PowerSource::class)
                                ->required(),
                            Select::make('operator_type')
                                ->label(__('specs.operator_type'))
                                ->options(OperatorType::class),
                            TextInput::make('power_watt')->label(__('specs.power_watt'))->numeric()->suffix('W'),
                            TextInput::make('voltage')->label(__('specs.voltage'))->numeric()->suffix('V'),
                            TextInput::make('tank_capacity_l')->label(__('specs.tank_capacity_l'))->numeric()->suffix('L'),
                            TextInput::make('recovery_tank_l')->label(__('specs.recovery_tank_l'))->numeric()->suffix('L'),
                            TextInput::make('suction_mbar')->label(__('specs.suction_mbar'))->numeric()->suffix('mbar'),
                            TextInput::make('airflow_lpm')->label(__('specs.airflow_lpm'))->numeric()->suffix('L/min'),
                            TextInput::make('cleaning_width_mm')->label(__('specs.cleaning_width_mm'))->numeric()->suffix('mm'),
                            TextInput::make('productivity_sqm_h')
                                ->label(__('specs.productivity_sqm_h'))
                                ->numeric()
                                ->suffix('m²/h')
                                ->helperText(__('admin.help.productivity')),
                            TextInput::make('battery_runtime_min')->label(__('specs.battery_runtime_min'))->numeric()->suffix('min'),
                            TextInput::make('water_pressure_bar')->label(__('specs.water_pressure_bar'))->numeric()->suffix('bar'),
                            TextInput::make('steam_temperature_c')->label(__('specs.steam_temperature_c'))->numeric()->suffix('°C'),
                            TextInput::make('noise_db')->label(__('specs.noise_db'))->numeric()->suffix('dB'),
                            TextInput::make('weight_kg')->label(__('specs.weight_kg'))->numeric()->suffix('kg'),
                        ]),

                    Section::make(__('admin.section.extra_specs'))
                        ->description(__('admin.section.extra_specs_hint'))
                        ->schema([
                            Repeater::make('specs')
                                ->relationship()
                                ->hiddenLabel()
                                ->columns(4)
                                ->reorderable()
                                ->collapsed()
                                ->itemLabel(fn (array $state) => $state['label']['fa'] ?? null)
                                ->schema([
                                    TextInput::make('group')->label(__('admin.field.group'))->default('general'),
                                    TextInput::make('label.fa')->label(__('admin.field.label_fa'))->required(),
                                    TextInput::make('value.fa')->label(__('admin.field.value_fa'))->required(),
                                    TextInput::make('unit')->label(__('admin.field.unit')),
                                    TextInput::make('label.en')->label(__('admin.field.label_en')),
                                    TextInput::make('value.en')->label(__('admin.field.value_en')),
                                    Toggle::make('is_highlighted')->label(__('admin.field.highlighted')),
                                ]),
                        ]),
                ]),

                Tabs\Tab::make(__('admin.tab.media'))->schema([
                    FileUpload::make('cover_image')
                        ->label(__('admin.field.cover_image'))
                        ->image()
                        ->imageEditor()
                        ->directory('products/covers'),

                    Repeater::make('images')
                        ->relationship('galleryImages')
                        ->label(__('admin.field.gallery'))
                        ->columns(3)
                        ->reorderable()
                        ->collapsed()
                        ->schema([
                            FileUpload::make('path')->label(__('admin.field.image'))->image()->directory('products/gallery')->required(),
                            TextInput::make('alt.fa')->label(__('admin.field.alt_fa')),
                            TextInput::make('alt.en')->label(__('admin.field.alt_en')),
                        ]),

                    Repeater::make('videos')
                        ->relationship()
                        ->label(__('admin.field.videos'))
                        ->columns(3)
                        ->collapsed()
                        ->schema([
                            Select::make('provider')
                                ->label(__('admin.field.provider'))
                                ->options(['aparat' => 'آپارات', 'youtube' => 'YouTube', 'file' => __('admin.field.uploaded_file')])
                                ->default('aparat')
                                ->required(),
                            TextInput::make('url')->label(__('admin.field.video_url'))->required(),
                            TextInput::make('title.fa')->label(__('admin.field.title_fa')),
                        ]),

                    Repeater::make('frames360')
                        ->relationship('frames360')
                        ->label(__('admin.field.frames_360'))
                        ->helperText(__('admin.help.frames_360'))
                        ->reorderable()
                        ->collapsed()
                        ->schema([
                            FileUpload::make('path')->image()->directory('products/360')->required(),
                            Toggle::make('is_360_frame')->default(true)->hidden()->dehydrated(),
                        ]),
                ]),

                Tabs\Tab::make(__('admin.tab.commerce'))->schema([
                    Section::make(__('admin.section.pricing'))
                        ->description(__('admin.section.pricing_hint'))
                        ->columns(3)
                        ->schema([
                            TextInput::make('price')->label(__('admin.field.price'))->numeric()->suffix('ریال'),
                            TextInput::make('compare_at_price')->label(__('admin.field.compare_at_price'))->numeric()->suffix('ریال'),
                            Toggle::make('price_visible')->label(__('admin.field.price_visible')),
                        ]),

                    Grid::make(3)->schema([
                        Select::make('stock_status')->label(__('admin.field.stock_status'))->options(StockStatus::class)->required(),
                        TextInput::make('warranty_months')->label(__('admin.field.warranty_months'))->numeric()->default(12),
                        TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                    ]),

                    Grid::make(3)->schema([
                        Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
                        Toggle::make('is_featured')->label(__('admin.field.is_featured')),
                        Toggle::make('is_rentable')->label(__('admin.field.is_rentable'))->live(),
                    ]),

                    Section::make(__('admin.section.rental'))
                        ->visible(fn ($get) => (bool) $get('is_rentable'))
                        ->schema([
                            Repeater::make('rentalPlans')
                                ->relationship()
                                ->hiddenLabel()
                                ->columns(4)
                                ->schema([
                                    Select::make('period')
                                        ->label(__('admin.field.period'))
                                        ->options(RentalPeriod::class)
                                        ->required(),
                                    TextInput::make('price')->label(__('admin.field.price'))->numeric(),
                                    TextInput::make('deposit')->label(__('admin.field.deposit'))->numeric(),
                                    TextInput::make('min_duration')->label(__('admin.field.min_duration'))->numeric()->default(1),
                                ]),
                        ]),

                    Select::make('environments')
                        ->label(__('admin.field.environments'))
                        ->relationship('environments', 'slug')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->multiple()
                        ->preload()
                        ->helperText(__('admin.help.environments')),
                ]),

                Tabs\Tab::make(__('admin.tab.seo'))->schema([
                    TranslatableFields::tabs('seo_title', __('admin.field.seo_title'), 'text', false),
                    TranslatableFields::tabs('seo_description', __('admin.field.seo_description'), 'textarea', false),
                    TextInput::make('seo_keywords')->label(__('admin.field.seo_keywords')),

                    Section::make(__('admin.section.faq'))
                        ->description(__('admin.section.faq_hint'))
                        ->schema([
                            Repeater::make('faqs')
                                ->relationship()
                                ->hiddenLabel()
                                ->collapsed()
                                ->itemLabel(fn (array $state) => $state['question']['fa'] ?? null)
                                ->schema([
                                    TextInput::make('question.fa')->label(__('admin.field.question'))->required(),
                                    TranslatableFields::field('textarea', 'answer.fa')->label(__('admin.field.answer'))->required(),
                                    Toggle::make('in_schema')->label(__('admin.field.in_schema'))->default(true),
                                ]),
                        ]),
                ]),
            ]),
        ]);
    }

    /**
     * Slugs are the URL contract with search engines, so they are generated
     * once on create and never silently rewritten afterwards.
     */
    public static function suggestSlug(?string $name): ?string
    {
        return $name ? Str::slug($name) : null;
    }
}
