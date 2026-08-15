<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Environment;
use App\Services\MachineSelectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineSelectorServiceTest extends TestCase
{
    use RefreshDatabase;

    private MachineSelectorService $selector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->selector = new MachineSelectorService;
    }

    public function test_it_sizes_required_productivity_from_area_and_shift(): void
    {
        // 5,000 m² over 8 hours at the 0.70 default efficiency.
        $result = $this->selector->recommend(area: 5000, shiftHours: 8);

        $this->assertSame(893, $result['required_productivity']);
        $this->assertSame(0.70, $result['efficiency']);
    }

    public function test_a_congested_environment_demands_a_faster_machine(): void
    {
        $warehouse = Environment::create([
            'slug' => 'warehouse',
            'name' => ['fa' => 'انبار'],
        ]);

        $mall = Environment::create([
            'slug' => 'shopping-mall',
            'name' => ['fa' => 'مرکز خرید'],
        ]);

        $inWarehouse = $this->selector->recommend(5000, 8, $warehouse);
        $inMall = $this->selector->recommend(5000, 8, $mall);

        // A mall loses more time to manoeuvring, so the same area needs a
        // higher rated productivity to finish in the same shift.
        $this->assertGreaterThan(
            $inWarehouse['required_productivity'],
            $inMall['required_productivity'],
        );
    }

    public function test_it_classifies_machines_by_floor_area(): void
    {
        $this->assertSame('manual', $this->selector->classifyByArea(300)['key']);
        $this->assertSame('walk_behind_compact', $this->selector->classifyByArea(1500)['key']);
        $this->assertSame('walk_behind_large', $this->selector->classifyByArea(4000)['key']);
        $this->assertSame('ride_on', $this->selector->classifyByArea(12000)['key']);
        $this->assertSame('ride_on_heavy', $this->selector->classifyByArea(90000)['key']);
    }

    public function test_it_clamps_nonsensical_inputs(): void
    {
        $result = $this->selector->recommend(area: 0, shiftHours: 0);

        $this->assertSame(1, $result['area']);
        $this->assertSame(1, $result['shift_hours']);
    }
}
