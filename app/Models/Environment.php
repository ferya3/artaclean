<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A place a machine gets used — factory, warehouse, hospital, mall, hotel,
 * parking, airport, car wash. The storefront's "دستگاه مناسب برای ..." block
 * and the sizing calculator both pivot on this.
 */
class Environment extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = [
        'name', 'short_description', 'description', 'challenges', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'short_description' => 'array',
            'description' => 'array',
            'challenges' => 'array',
            'seo_title' => 'array',
            'seo_description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('suitability');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('suitability');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function url(): string
    {
        return route('environment.show', $this->slug);
    }

    /**
     * The challenges column holds a locale => list-of-strings map, so the
     * translation helper (which expects strings) is bypassed here.
     */
    public function challengeList(): array
    {
        $raw = $this->getTranslations('challenges');
        $value = $raw[app()->getLocale()] ?? $raw[config('app.fallback_locale')] ?? reset($raw);

        return is_array($value) ? $value : [];
    }
}
