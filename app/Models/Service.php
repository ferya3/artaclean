<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Installation, maintenance, repair, parts, training, rental, warranty.
 *
 * In industrial equipment these are not a footer link — they are half of what
 * the buyer is choosing between suppliers on, so they get their own section
 * of the site and their own pages.
 */
class Service extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = [
        'name', 'short_description', 'description', 'bullets', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'short_description' => 'array',
            'description' => 'array',
            'bullets' => 'array',
            'seo_title' => 'array',
            'seo_description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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
        return route('service.show', $this->slug);
    }

    /** @return array<int, string> */
    public function bulletList(): array
    {
        $raw = $this->getTranslations('bullets');
        $value = $raw[app()->getLocale()] ?? $raw[config('app.fallback_locale')] ?? reset($raw);

        return is_array($value) ? $value : [];
    }

    /** @return array<int, array{title: string, url: string|null}> */
    public function breadcrumbs(): array
    {
        return [
            ['title' => __('nav.home'), 'url' => route('home')],
            ['title' => __('nav.services'), 'url' => route('services.index')],
            ['title' => $this->name, 'url' => $this->url()],
        ];
    }
}
