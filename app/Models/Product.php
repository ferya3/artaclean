<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OperatorType;
use App\Enums\PowerSource;
use App\Enums\StockStatus;
use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use HasTranslations;
    use Searchable;
    use SoftDeletes;

    protected $guarded = [];

    protected array $translatable = [
        'name', 'short_description', 'description', 'highlights', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'short_description' => 'array',
            'description' => 'array',
            'highlights' => 'array',
            'seo_title' => 'array',
            'seo_description' => 'array',
            'price' => 'decimal:0',
            'compare_at_price' => 'decimal:0',
            'price_visible' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_rentable' => 'boolean',
            'power_source' => PowerSource::class,
            'operator_type' => OperatorType::class,
            'stock_status' => StockStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function galleryImages(): HasMany
    {
        return $this->images()->where('is_360_frame', false);
    }

    public function frames360(): HasMany
    {
        return $this->images()->where('is_360_frame', true);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('sort_order');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class)->orderBy('sort_order');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->withPivot('numeric_value');
    }

    public function environments(): BelongsToMany
    {
        return $this->belongsToMany(Environment::class)->withPivot('suitability');
    }

    public function rentalPlans(): HasMany
    {
        return $this->hasMany(RentalPlan::class)->where('is_active', true);
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->where('is_active', true)->orderBy('sort_order');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRentable(Builder $query): Builder
    {
        return $query->where('is_rentable', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeForEnvironment(Builder $query, Environment|int $environment): Builder
    {
        $id = $environment instanceof Environment ? $environment->id : $environment;

        return $query->whereHas('environments', fn (Builder $q) => $q->where('environments.id', $id));
    }

    /**
     * Machines that can realistically clean the given floor area in one shift.
     * Assumes an 8 hour shift at 70% of the rated productivity, which is the
     * rule of thumb the industry catalogs use.
     */
    public function scopeSuitableForArea(Builder $query, int $squareMeters, int $shiftHours = 8): Builder
    {
        $required = (int) ceil($squareMeters / max($shiftHours, 1) / 0.7);

        return $query->where('productivity_sqm_h', '>=', $required);
    }

    // -----------------------------------------------------------------------
    // Presentation
    // -----------------------------------------------------------------------

    /**
     * Products live under their category so the URL carries the same keyword
     * hierarchy the breadcrumbs do: /scrubber-dryer/ana-70l.
     */
    public function url(): string
    {
        return route('product.show', [
            'category' => $this->category?->slug ?? 'products',
            'product' => $this->slug,
        ]);
    }

    public function coverUrl(): string
    {
        if ($this->cover_image) {
            return Storage::url($this->cover_image);
        }

        // Until the photographs land, show this category's machine.
        return $this->category?->illustrationUrl() ?? asset('images/placeholder-product.svg');
    }

    /**
     * Iranian equipment prices move week to week, so a number is only shown
     * when both the site policy and the product itself allow it.
     */
    public function showsPrice(): bool
    {
        return config('site.prices_public')
            && $this->price_visible
            && $this->price !== null
            && (float) $this->price > 0;
    }

    public function highlightList(): array
    {
        $raw = $this->getTranslations('highlights');
        $value = $raw[app()->getLocale()] ?? $raw[config('app.fallback_locale')] ?? reset($raw);

        return is_array($value) ? $value : [];
    }

    /**
     * The handful of numbers a buyer scans first — rendered as the spec strip
     * under the gallery and as the rows of the comparison table.
     *
     * @return Collection<int, array{key: string, label: string, value: string}>
     */
    public function keySpecs(): Collection
    {
        return collect([
            ['key' => 'power_watt', 'value' => $this->power_watt, 'unit' => 'W'],
            ['key' => 'tank_capacity_l', 'value' => $this->tank_capacity_l, 'unit' => 'L'],
            ['key' => 'suction_mbar', 'value' => $this->suction_mbar, 'unit' => 'mbar'],
            ['key' => 'airflow_lpm', 'value' => $this->airflow_lpm, 'unit' => 'L/min'],
            ['key' => 'cleaning_width_mm', 'value' => $this->cleaning_width_mm, 'unit' => 'mm'],
            ['key' => 'productivity_sqm_h', 'value' => $this->productivity_sqm_h, 'unit' => 'm²/h'],
            ['key' => 'battery_runtime_min', 'value' => $this->battery_runtime_min, 'unit' => 'min'],
            ['key' => 'water_pressure_bar', 'value' => $this->water_pressure_bar, 'unit' => 'bar'],
            ['key' => 'steam_temperature_c', 'value' => $this->steam_temperature_c, 'unit' => '°C'],
            ['key' => 'noise_db', 'value' => $this->noise_db, 'unit' => 'dB'],
            ['key' => 'weight_kg', 'value' => $this->weight_kg, 'unit' => 'kg'],
        ])
            ->filter(fn (array $spec) => filled($spec['value']))
            ->map(fn (array $spec) => [
                'key' => $spec['key'],
                'label' => __('specs.'.$spec['key']),
                'value' => trim($spec['value'].' '.$spec['unit']),
                'raw' => $spec['value'],
            ])
            ->values();
    }

    // -----------------------------------------------------------------------
    // Scout
    // -----------------------------------------------------------------------

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name_fa' => $this->translate('name', 'fa'),
            'name_en' => $this->translate('name', 'en'),
            'sku' => $this->sku,
            'model_code' => $this->model_code,
            'short_description_fa' => $this->translate('short_description', 'fa'),
            'short_description_en' => $this->translate('short_description', 'en'),
            'brand' => $this->brand?->translate('name', 'fa'),
            'category' => $this->category?->translate('name', 'fa'),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }
}
