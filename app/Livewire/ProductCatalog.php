<?php

declare(strict_types=1);

namespace App\Livewire;

use App\CQRS\Bus\QueryBus;
use App\CQRS\Queries\GetProductCatalog;
use App\Enums\OperatorType;
use App\Enums\PowerSource;
use App\Models\Attribute;
use App\Support\ProductFilters;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The professional filter panel. Every control is bound to the query string so
 * a filtered view is a shareable, bookmarkable URL — the way a procurement
 * manager actually sends a shortlist to a colleague.
 */
class ProductCatalog extends Component
{
    use WithPagination;

    /** Fixed by the page (a category listing), not by the visitor. */
    public ?string $category = null;

    public ?string $environmentSlug = null;

    #[Url(as: 'brand', history: true, keep: false)]
    public array $brands = [];

    #[Url(as: 'power', history: true)]
    public array $powerSources = [];

    #[Url(as: 'type', history: true)]
    public array $operatorTypes = [];

    #[Url(as: 'spec', history: true)]
    public array $attributeValueIds = [];

    #[Url(as: 'tank_min', history: true)]
    public ?int $tankCapacityMin = null;

    #[Url(as: 'tank_max', history: true)]
    public ?int $tankCapacityMax = null;

    #[Url(as: 'power_min', history: true)]
    public ?int $powerMin = null;

    #[Url(as: 'power_max', history: true)]
    public ?int $powerMax = null;

    #[Url(as: 'width_min', history: true)]
    public ?int $cleaningWidthMin = null;

    #[Url(as: 'width_max', history: true)]
    public ?int $cleaningWidthMax = null;

    #[Url(as: 'area', history: true)]
    public ?int $coverageArea = null;

    #[Url(as: 'rental', history: true)]
    public bool $rentableOnly = false;

    #[Url(as: 'stock', history: true)]
    public bool $inStockOnly = false;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'default';

    public int $perPage = 12;

    public function updated(string $property): void
    {
        // Any filter change invalidates the current page number.
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'brands', 'powerSources', 'operatorTypes', 'attributeValueIds',
            'tankCapacityMin', 'tankCapacityMax', 'powerMin', 'powerMax',
            'cleaningWidthMin', 'cleaningWidthMax', 'coverageArea',
            'rentableOnly', 'inStockOnly', 'search',
        ]);

        $this->resetPage();
    }

    public function removeBrand(string $slug): void
    {
        $this->brands = array_values(array_diff($this->brands, [$slug]));
        $this->resetPage();
    }

    public function render(QueryBus $bus): View
    {
        $filters = $this->filters();

        $result = $bus->ask(new GetProductCatalog($filters, $this->perPage));

        return view('livewire.product-catalog', [
            'products' => $result['products'],
            'facets' => $result['facets'],
            'filters' => $filters,
            'powerSourceOptions' => PowerSource::options(),
            'operatorTypeOptions' => OperatorType::options(),
            'attributes' => $this->filterableAttributes(),
        ]);
    }

    private function filters(): ProductFilters
    {
        return ProductFilters::fromArray([
            'category' => $this->category,
            'environment' => $this->environmentSlug,
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
            'search' => $this->search !== '' ? $this->search : null,
            'sort' => $this->sort,
            'page' => $this->getPage(),
        ]);
    }

    /**
     * Only the attributes mapped to the category in view, so a steam machine
     * facet never appears on the spare-parts listing.
     */
    private function filterableAttributes()
    {
        return Attribute::query()
            ->filterable()
            ->with('values')
            ->when(
                $this->category,
                fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $this->category))
            )
            ->ordered()
            ->get();
    }
}
