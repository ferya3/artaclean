<?php

declare(strict_types=1);

namespace App\CQRS\Queries;

use App\CQRS\Query;
use App\Support\ProductFilters;

final readonly class GetProductCatalog implements Query
{
    public function __construct(
        public ProductFilters $filters,
        public int $perPage = 12,
        public bool $withFacets = true,
    ) {}
}
