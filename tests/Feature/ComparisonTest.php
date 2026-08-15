<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CompareButton;
use App\Livewire\CompareTable;
use App\Models\Category;
use App\Models\Product;
use App\Services\ComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComparisonTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, Product> */
    private array $products = [];

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['slug' => 'scrubber-dryer', 'name' => ['fa' => 'اسکرابر']]);

        foreach ([
            ['a', 1500, 220, 60],
            ['b', 3000, 250, 68],
            ['c', 5000, 250, 72],
            ['d', 800, 180, 58],
            ['e', 900, 200, 62],
        ] as [$suffix, $productivity, $suction, $noise]) {
            $this->products[] = Product::create([
                'category_id' => $category->id,
                'slug' => 'machine-'.$suffix,
                'name' => ['fa' => 'دستگاه '.$suffix],
                'power_source' => 'battery',
                'productivity_sqm_h' => $productivity,
                'suction_mbar' => $suction,
                'noise_db' => $noise,
                'is_active' => true,
            ]);
        }
    }

    public function test_a_product_can_be_added_and_removed(): void
    {
        $comparison = app(ComparisonService::class);

        Livewire::test(CompareButton::class, ['productId' => $this->products[0]->id])
            ->call('toggle')
            ->assertDispatched('comparison-updated');

        $this->assertTrue($comparison->has($this->products[0]->id));

        Livewire::test(CompareButton::class, ['productId' => $this->products[0]->id])
            ->call('toggle');

        $this->assertFalse($comparison->has($this->products[0]->id));
    }

    public function test_the_shortlist_is_capped(): void
    {
        $comparison = app(ComparisonService::class);

        foreach (array_slice($this->products, 0, 4) as $product) {
            $this->assertTrue($comparison->add($product->id));
        }

        // The fifth machine is refused rather than silently dropping one.
        $this->assertFalse($comparison->add($this->products[4]->id));
        $this->assertSame(ComparisonService::MAX_ITEMS, $comparison->count());

        Livewire::test(CompareButton::class, ['productId' => $this->products[4]->id])
            ->call('toggle')
            ->assertDispatched('toast');
    }

    public function test_the_table_flags_the_best_value_in_each_row(): void
    {
        $comparison = app(ComparisonService::class);
        $comparison->add($this->products[0]->id);
        $comparison->add($this->products[2]->id);

        $rows = collect($comparison->table($comparison->products()))->keyBy('key');

        // More productivity wins.
        $this->assertSame(1, $rows['productivity_sqm_h']['best']);

        // Less noise wins.
        $this->assertSame(0, $rows['noise_db']['best']);
    }

    public function test_a_tie_has_no_winner(): void
    {
        $comparison = app(ComparisonService::class);
        $comparison->add($this->products[1]->id);
        $comparison->add($this->products[2]->id);

        $rows = collect($comparison->table($comparison->products()))->keyBy('key');

        // Both machines pull 250 mbar, so neither is highlighted.
        $this->assertNull($rows['suction_mbar']['best']);
        $this->assertFalse($rows['suction_mbar']['differs']);
    }

    public function test_the_differences_only_toggle_hides_identical_rows(): void
    {
        $comparison = app(ComparisonService::class);
        $comparison->add($this->products[1]->id);
        $comparison->add($this->products[2]->id);

        $all = Livewire::test(CompareTable::class)->viewData('rows');
        $differing = Livewire::test(CompareTable::class)
            ->set('differencesOnly', true)
            ->viewData('rows');

        $this->assertLessThan(count($all), count($differing));
        $this->assertNotContains('suction_mbar', array_column($differing, 'key'));
    }

    public function test_the_shortlist_keeps_insertion_order(): void
    {
        $comparison = app(ComparisonService::class);
        $comparison->add($this->products[2]->id);
        $comparison->add($this->products[0]->id);

        $this->assertSame(
            [$this->products[2]->id, $this->products[0]->id],
            $comparison->products()->modelKeys(),
        );
    }
}
