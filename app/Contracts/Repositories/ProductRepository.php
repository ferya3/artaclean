<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Product;
use App\Support\ProductFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepository
{
    public function findBySlug(string $slug): ?Product;

    /** @param  array<int, int>  $ids */
    public function findManyForComparison(array $ids): Collection;

    public function paginateFiltered(ProductFilters $filters, int $perPage = 12): LengthAwarePaginator;

    public function featured(int $limit = 8): Collection;

    public function newest(int $limit = 8): Collection;

    public function relatedTo(Product $product, int $limit = 4): Collection;

    /** Products a given environment is tagged for, best fit first. */
    public function forEnvironment(int $environmentId, int $limit = 12): Collection;

    /** Distinct facet buckets for the current filter set, with counts. */
    public function facetsFor(ProductFilters $filters): array;

    public function incrementViews(Product $product): void;
}
