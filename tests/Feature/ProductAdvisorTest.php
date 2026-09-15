<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\MachineAdvisor;
use App\Services\ProductAdvisor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SolutionArchitectureSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductAdvisorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            UserSeeder::class,
            CatalogSeeder::class,
            ProductSeeder::class,
            SolutionArchitectureSeeder::class,
        ]);
    }

    public function test_surface_is_a_hard_filter(): void
    {
        $result = app(ProductAdvisor::class)->recommend(surface: 'carpet');

        $this->assertGreaterThan(0, $result['total']);

        foreach ([$result['best'], ...$result['alternatives']] as $product) {
            $this->assertContains('carpet', $product->surface_types);
        }
    }

    public function test_the_smallest_sufficient_machine_wins(): void
    {
        // A 500 m² floor needs about 90 m²/h. The ride-on rated at thousands
        // could do it, but recommending it would be the exact mistake this
        // tool exists to prevent.
        $result = app(ProductAdvisor::class)->recommend(
            surface: 'concrete',
            soil: 'dust',
            areaBand: 'under_500',
        );

        $this->assertNotNull($result['best']);
        $this->assertNotSame('ride_on', $result['best']->operator_type?->value);
    }

    public function test_a_machine_that_cannot_finish_the_shift_is_ranked_below_one_that_can(): void
    {
        $result = app(ProductAdvisor::class)->recommend(
            surface: 'concrete',
            soil: 'oil',
            areaBand: 'over_10000',
        );

        $this->assertNotNull($result['best']);
        $this->assertGreaterThanOrEqual(
            $result['required_productivity'],
            (int) $result['best']->productivity_sqm_h,
        );
    }

    public function test_the_soil_filter_is_relaxed_rather_than_returning_nothing(): void
    {
        // Nothing in the catalogue is rated for chemicals on a carpet.
        $result = app(ProductAdvisor::class)->recommend(surface: 'carpet', soil: 'chemical');

        $this->assertTrue($result['relaxed']);
        $this->assertNotNull($result['best']);
    }

    public function test_the_component_walks_the_four_steps_and_recommends(): void
    {
        Livewire::test(MachineAdvisor::class)
            ->call('choose', 'surface', 'concrete')
            ->assertSet('step', 2)
            ->call('choose', 'soil', 'oil')
            ->assertSet('step', 3)
            ->call('choose', 'areaBand', '2000_10000')
            ->assertSet('step', 4)
            ->call('choose', 'frequency', 'daily')
            ->call('find')
            ->assertSet('result.relaxed', false)
            ->assertSee(__('advisor.best_match'));
    }

    public function test_choosing_the_same_answer_twice_clears_it(): void
    {
        Livewire::test(MachineAdvisor::class)
            ->call('choose', 'surface', 'concrete')
            ->assertSet('surface', 'concrete')
            ->call('goTo', 1)
            ->call('choose', 'surface', 'concrete')
            ->assertSet('surface', null);
    }
}
