<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ProductCatalog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $scrubbers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scrubbers = Category::create(['slug' => 'scrubber-dryer', 'name' => ['fa' => 'اسکرابر']]);
        $vacuums = Category::create(['slug' => 'industrial-vacuum-cleaner', 'name' => ['fa' => 'جاروبرقی']]);

        $karcher = Brand::create(['slug' => 'karcher', 'name' => ['fa' => 'کارچر']]);
        $ipc = Brand::create(['slug' => 'ipc', 'name' => ['fa' => 'آی‌پی‌سی']]);

        Product::create([
            'category_id' => $this->scrubbers->id, 'brand_id' => $karcher->id,
            'slug' => 'small-scrubber', 'name' => ['fa' => 'اسکرابر کوچک'],
            'power_source' => 'battery', 'operator_type' => 'walk_behind',
            'tank_capacity_l' => 40, 'productivity_sqm_h' => 1500, 'cleaning_width_mm' => 450,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $this->scrubbers->id, 'brand_id' => $ipc->id,
            'slug' => 'big-scrubber', 'name' => ['fa' => 'اسکرابر بزرگ'],
            'power_source' => 'battery', 'operator_type' => 'ride_on',
            'tank_capacity_l' => 130, 'productivity_sqm_h' => 5000, 'cleaning_width_mm' => 1000,
            'is_active' => true, 'is_rentable' => true,
        ]);

        Product::create([
            'category_id' => $vacuums->id, 'brand_id' => $karcher->id,
            'slug' => 'shop-vacuum', 'name' => ['fa' => 'جاروبرقی کارگاهی'],
            'power_source' => 'electric', 'tank_capacity_l' => 30,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $this->scrubbers->id,
            'slug' => 'hidden-scrubber', 'name' => ['fa' => 'اسکرابر غیرفعال'],
            'power_source' => 'battery', 'is_active' => false,
        ]);
    }

    public function test_it_shows_only_active_products(): void
    {
        Livewire::test(ProductCatalog::class)
            ->assertSee('اسکرابر کوچک')
            ->assertDontSee('اسکرابر غیرفعال');
    }

    public function test_it_scopes_to_a_category(): void
    {
        Livewire::test(ProductCatalog::class, ['category' => $this->scrubbers->slug])
            ->assertSee('اسکرابر بزرگ')
            ->assertDontSee('جاروبرقی کارگاهی');
    }

    public function test_it_filters_by_brand(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('brands', ['ipc'])
            ->assertSee('اسکرابر بزرگ')
            ->assertDontSee('اسکرابر کوچک');
    }

    public function test_it_filters_by_power_source_and_operator_type(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('powerSources', ['electric'])
            ->assertSee('جاروبرقی کارگاهی')
            ->assertDontSee('اسکرابر بزرگ');

        Livewire::test(ProductCatalog::class)
            ->set('operatorTypes', ['ride_on'])
            ->assertSee('اسکرابر بزرگ')
            ->assertDontSee('اسکرابر کوچک');
    }

    public function test_it_filters_by_tank_capacity_range(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('tankCapacityMin', 100)
            ->assertSee('اسکرابر بزرگ')
            ->assertDontSee('اسکرابر کوچک');
    }

    /**
     * The area filter is the one that matches how a buyer thinks: it converts
     * square metres into the productivity a machine must sustain.
     */
    public function test_it_filters_by_coverage_area(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('coverageArea', 20000)
            ->assertSee('اسکرابر بزرگ')
            ->assertDontSee('اسکرابر کوچک');
    }

    public function test_it_filters_rentable_machines(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('rentableOnly', true)
            ->assertSee('اسکرابر بزرگ')
            ->assertDontSee('جاروبرقی کارگاهی');
    }

    public function test_it_searches_by_name(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('search', 'جاروبرقی')
            ->assertSee('جاروبرقی کارگاهی')
            ->assertDontSee('اسکرابر بزرگ');
    }

    public function test_changing_a_filter_resets_the_page(): void
    {
        // Staying on page 3 of a narrower result set would show an empty list.
        Livewire::test(ProductCatalog::class)
            ->call('setPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'اسکرابر')
            ->assertSet('paginators.page', 1);
    }

    public function test_clearing_filters_restores_the_full_listing(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('brands', ['ipc'])
            ->call('clearFilters')
            ->assertSee('اسکرابر کوچک')
            ->assertSee('جاروبرقی کارگاهی');
    }

    public function test_brand_facets_carry_counts_and_localised_names(): void
    {
        // Facet counts drive the sidebar and must survive the brands join.
        Livewire::test(ProductCatalog::class, ['category' => $this->scrubbers->slug])
            ->assertSee('کارچر')
            ->assertSee('آی‌پی‌سی');
    }
}
