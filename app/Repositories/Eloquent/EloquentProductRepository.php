<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepository;
use App\Models\Product;
use App\Support\ProductFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentProductRepository implements ProductRepository
{
    public function findBySlug(string $slug): ?Product
    {
        return Product::query()
            ->active()
            ->with([
                'category.parent',
                'brand',
                'galleryImages',
                'frames360',
                'videos',
                'specs',
                'downloads' => fn ($q) => $q->public(),
                'faqs',
                'environments',
                'rentalPlans',
                'blogs' => fn ($q) => $q->published()->limit(3),
            ])
            ->where('slug', $slug)
            ->first();
    }

    public function findManyForComparison(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->active()
            ->with(['brand', 'category', 'specs'])
            ->whereIn('id', $ids)
            // Keep the order the visitor added them in.
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
            ->values();
    }

    public function paginateFiltered(ProductFilters $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->with(['brand', 'category'])
            ->tap(fn (Builder $query) => $this->applySort($query, $filters->sort))
            ->paginate($perPage, ['*'], 'page', $filters->page)
            ->withQueryString();
    }

    public function featured(int $limit = 8): Collection
    {
        return Product::query()
            ->active()
            ->featured()
            ->with(['brand', 'category'])
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function newest(int $limit = 8): Collection
    {
        return Product::query()
            ->active()
            ->with(['brand', 'category'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function relatedTo(Product $product, int $limit = 4): Collection
    {
        return Product::query()
            ->active()
            ->with(['brand', 'category'])
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            // Closest productivity rating first — that is the axis a buyer is
            // actually cross-shopping on.
            ->orderByRaw('ABS(COALESCE(productivity_sqm_h, 0) - ?)', [$product->productivity_sqm_h ?? 0])
            ->limit($limit)
            ->get();
    }

    public function forEnvironment(int $environmentId, int $limit = 12): Collection
    {
        return Product::query()
            ->active()
            ->with(['brand', 'category'])
            ->whereHas('environments', fn (Builder $q) => $q->where('environments.id', $environmentId))
            ->join('environment_product', 'environment_product.product_id', '=', 'products.id')
            ->where('environment_product.environment_id', $environmentId)
            ->orderByDesc('environment_product.suitability')
            ->orderBy('products.sort_order')
            ->limit($limit)
            ->select('products.*')
            ->get();
    }

    public function facetsFor(ProductFilters $filters): array
    {
        // Facet counts ignore the facet they describe, so unselecting a brand
        // is always reachable from the UI.
        $withoutBrands = ProductFilters::fromArray(['brands' => []] + $filters->toArray());

        $brands = $this->baseQuery($withoutBrands)
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->groupBy('brands.id', 'brands.slug', 'brands.name')
            ->select('brands.slug', 'brands.name', DB::raw('COUNT(products.id) as total'))
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->slug => [
                    'name' => $this->localized($row->name) ?? $row->slug,
                    'count' => (int) $row->total,
                ],
            ])
            ->all();

        $withoutPower = ProductFilters::fromArray(['power_sources' => []] + $filters->toArray());

        $powerSources = $this->baseQuery($withoutPower)
            ->groupBy('power_source')
            ->select('power_source', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'power_source')
            ->all();

        $withoutOperator = ProductFilters::fromArray(['operator_types' => []] + $filters->toArray());

        $operatorTypes = $this->baseQuery($withoutOperator)
            ->whereNotNull('operator_type')
            ->groupBy('operator_type')
            ->select('operator_type', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'operator_type')
            ->all();

        $ranges = $this->baseQuery($filters)
            ->selectRaw('MIN(tank_capacity_l) as tank_min, MAX(tank_capacity_l) as tank_max')
            ->selectRaw('MIN(power_watt) as power_min, MAX(power_watt) as power_max')
            ->selectRaw('MIN(cleaning_width_mm) as width_min, MAX(cleaning_width_mm) as width_max')
            ->first();

        return [
            'brands' => $brands,
            'power_sources' => $powerSources,
            'operator_types' => $operatorTypes,
            'ranges' => [
                'tank' => ['min' => (int) ($ranges->tank_min ?? 0), 'max' => (int) ($ranges->tank_max ?? 0)],
                'power' => ['min' => (int) ($ranges->power_min ?? 0), 'max' => (int) ($ranges->power_max ?? 0)],
                'width' => ['min' => (int) ($ranges->width_min ?? 0), 'max' => (int) ($ranges->width_max ?? 0)],
            ],
        ];
    }

    public function incrementViews(Product $product): void
    {
        // No touch() — a view must not bump updated_at and invalidate caches.
        Product::withoutTimestamps(fn () => $product->increment('views_count'));
    }

    // -----------------------------------------------------------------------

    /**
     * Resolves a raw translatable JSON column selected through a join, where
     * no Eloquent cast is in play.
     */
    private function localized(?string $json): ?string
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return $json;
        }

        foreach ([app()->getLocale(), config('app.fallback_locale')] as $locale) {
            if (! empty($decoded[$locale])) {
                return (string) $decoded[$locale];
            }
        }

        $first = reset($decoded);

        return $first === false ? null : (string) $first;
    }

    private function baseQuery(ProductFilters $filters): Builder
    {
        // Columns are table-qualified because the facet queries join `brands`,
        // which carries its own `is_active` and `sort_order`.
        $query = Product::query()->from('products')->where('products.is_active', true);

        if ($filters->category) {
            $query->whereHas('category', function (Builder $q) use ($filters) {
                $q->where('slug', $filters->category)
                    ->orWhereHas('parent', fn (Builder $p) => $p->where('slug', $filters->category));
            });
        }

        if ($filters->environment) {
            $query->whereHas(
                'environments',
                fn (Builder $q) => $q->where('environments.slug', $filters->environment)
            );
        }

        if ($filters->brands !== []) {
            $query->whereHas('brand', fn (Builder $q) => $q->whereIn('slug', $filters->brands));
        }

        if ($filters->powerSources !== []) {
            $query->whereIn('products.power_source', $filters->powerSources);
        }

        if ($filters->operatorTypes !== []) {
            $query->whereIn('products.operator_type', $filters->operatorTypes);
        }

        foreach ($filters->attributeValueIds as $valueId) {
            // AND across attributes: each selected value must be present.
            $query->whereHas(
                'attributeValues',
                fn (Builder $q) => $q->where('attribute_values.id', $valueId)
            );
        }

        $this->applyRange($query, 'tank_capacity_l', $filters->tankCapacityMin, $filters->tankCapacityMax);
        $this->applyRange($query, 'power_watt', $filters->powerMin, $filters->powerMax);
        $this->applyRange($query, 'cleaning_width_mm', $filters->cleaningWidthMin, $filters->cleaningWidthMax);

        if ($filters->coverageArea) {
            $query->suitableForArea($filters->coverageArea);
        }

        if ($filters->rentableOnly) {
            $query->rentable();
        }

        if ($filters->inStockOnly) {
            $query->where('products.stock_status', 'in_stock');
        }

        if (filled($filters->search)) {
            $term = '%'.$filters->search.'%';

            // Translatable columns are JSON with escaped unicode, so a plain
            // LIKE against the raw column can never match a Persian term. The
            // `->` path syntax compiles to json_extract on both MySQL 8 and
            // SQLite, which decodes the escape sequences first.
            $query->where(function (Builder $q) use ($term) {
                foreach (array_keys(config('site.locales')) as $locale) {
                    $q->orWhere("products.name->{$locale}", 'like', $term)
                        ->orWhere("products.short_description->{$locale}", 'like', $term);
                }

                $q->orWhere('products.sku', 'like', $term)
                    ->orWhere('products.model_code', 'like', $term);
            });
        }

        return $query;
    }

    private function applyRange(Builder $query, string $column, ?int $min, ?int $max): void
    {
        if ($min !== null) {
            $query->where('products.'.$column, '>=', $min);
        }

        if ($max !== null) {
            $query->where('products.'.$column, '<=', $max);
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->latest('products.id'),
            'popular' => $query->orderByDesc('views_count'),
            'productivity' => $query->orderByDesc('productivity_sqm_h'),
            'tank' => $query->orderByDesc('tank_capacity_l'),
            'power' => $query->orderByDesc('power_watt'),
            'price_asc' => $query->orderByRaw('price IS NULL, price ASC'),
            'price_desc' => $query->orderByRaw('price IS NULL, price DESC'),
            default => $query->orderBy('products.sort_order')->orderByDesc('products.id'),
        };
    }
}
