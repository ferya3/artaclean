<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The filter state a catalog listing carries. Built once from the request (or
 * from Livewire's URL-bound properties) and passed down through the query
 * handler into the repository, so the same shape drives the listing, the facet
 * counts and the cache key.
 */
final class ProductFilters
{
    /**
     * @param  array<int, string>  $brands  brand slugs
     * @param  array<int, string>  $powerSources
     * @param  array<int, string>  $operatorTypes
     * @param  array<int, int>  $attributeValueIds
     */
    public function __construct(
        public readonly ?string $category = null,
        public readonly ?string $environment = null,
        public readonly array $brands = [],
        public readonly array $powerSources = [],
        public readonly array $operatorTypes = [],
        public readonly array $attributeValueIds = [],
        public readonly ?int $tankCapacityMin = null,
        public readonly ?int $tankCapacityMax = null,
        public readonly ?int $powerMin = null,
        public readonly ?int $powerMax = null,
        public readonly ?int $cleaningWidthMin = null,
        public readonly ?int $cleaningWidthMax = null,
        public readonly ?int $coverageArea = null,
        public readonly bool $rentableOnly = false,
        public readonly bool $inStockOnly = false,
        public readonly ?string $search = null,
        public readonly string $sort = 'default',
        public readonly int $page = 1,
    ) {}

    public static function fromArray(array $data): self
    {
        $ints = static fn (string $key) => isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null
            ? (int) $data[$key]
            : null;

        $list = static fn (string $key) => array_values(array_filter((array) ($data[$key] ?? [])));

        return new self(
            category: $data['category'] ?? null,
            environment: $data['environment'] ?? null,
            brands: $list('brands'),
            powerSources: $list('power_sources'),
            operatorTypes: $list('operator_types'),
            attributeValueIds: array_map('intval', $list('attribute_values')),
            tankCapacityMin: $ints('tank_capacity_min'),
            tankCapacityMax: $ints('tank_capacity_max'),
            powerMin: $ints('power_min'),
            powerMax: $ints('power_max'),
            cleaningWidthMin: $ints('cleaning_width_min'),
            cleaningWidthMax: $ints('cleaning_width_max'),
            coverageArea: $ints('coverage_area'),
            rentableOnly: (bool) ($data['rentable_only'] ?? false),
            inStockOnly: (bool) ($data['in_stock_only'] ?? false),
            search: $data['search'] ?? null,
            sort: $data['sort'] ?? 'default',
            page: max(1, (int) ($data['page'] ?? 1)),
        );
    }

    public function withCategory(?string $category): self
    {
        return self::fromArray(['category' => $category] + $this->toArray());
    }

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'environment' => $this->environment,
            'brands' => $this->brands,
            'power_sources' => $this->powerSources,
            'operator_types' => $this->operatorTypes,
            'attribute_values' => $this->attributeValueIds,
            'tank_capacity_min' => $this->tankCapacityMin,
            'tank_capacity_max' => $this->tankCapacityMax,
            'power_min' => $this->powerMin,
            'power_max' => $this->powerMax,
            'cleaning_width_min' => $this->cleaningWidthMin,
            'cleaning_width_max' => $this->cleaningWidthMax,
            'coverage_area' => $this->coverageArea,
            'rentable_only' => $this->rentableOnly,
            'in_stock_only' => $this->inStockOnly,
            'search' => $this->search,
            'sort' => $this->sort,
            'page' => $this->page,
        ];
    }

    public function signature(): string
    {
        return md5(json_encode($this->toArray()).'|'.app()->getLocale());
    }

    public function hasActiveFilters(): bool
    {
        $state = $this->toArray();
        unset($state['category'], $state['sort'], $state['page']);

        return collect($state)->filter(fn ($value) => $value !== null && $value !== [] && $value !== false && $value !== '')
            ->isNotEmpty();
    }
}
