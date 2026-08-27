<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['name', 'short_description', 'description', 'seo_title', 'seo_description'];

    /** @var array<int, string>|null Slugs that have a machine illustration on disk. */
    private static ?array $illustrations = null;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'short_description' => 'array',
            'description' => 'array',
            'seo_title' => 'array',
            'seo_description' => 'array',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class);
    }

    public function environments(): BelongsToMany
    {
        return $this->belongsToMany(Environment::class)->withPivot('suitability');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->where('is_active', true)->orderBy('sort_order');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInMenu(Builder $query): Builder
    {
        return $query->where('show_in_menu', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Categories sit at the URL root — /industrial-vacuum-cleaner — because
     * that is what the keyword research for this niche rewards.
     */
    public function url(): string
    {
        return route('category.show', $this->slug);
    }

    /**
     * The drawing that stands in for this category's machine.
     *
     * Every category ships one, so a product still waiting on its photographs
     * shows the machine it actually is rather than the single grey silhouette
     * the whole catalogue used to share. Files are listed once per request:
     * a catalogue page renders this a few dozen times.
     */
    public function illustrationUrl(): string
    {
        self::$illustrations ??= array_map(
            static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
            glob(public_path('images/machines/*.svg')) ?: [],
        );

        return in_array($this->slug, self::$illustrations, true)
            ? asset("images/machines/{$this->slug}.svg")
            : asset('images/placeholder-product.svg');
    }

    /** @return array<int, array{title: string, url: string|null}> */
    public function breadcrumbs(): array
    {
        $trail = [];
        $node = $this;

        while ($node !== null) {
            array_unshift($trail, ['title' => $node->name, 'url' => $node->url()]);
            $node = $node->parent;
        }

        array_unshift($trail, ['title' => __('nav.products'), 'url' => route('products.index')]);
        array_unshift($trail, ['title' => __('nav.home'), 'url' => route('home')]);

        return $trail;
    }
}
