<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Environment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * "محاسبه‌گر انتخاب دستگاه" — turn a floor area, a shift length and an
 * environment into a concrete machine class and a shortlist of products.
 *
 * The sizing model follows the way the industry catalogs size equipment:
 *
 *   required_productivity (m²/h) = area / shift_hours / efficiency
 *
 * where efficiency accounts for turning, obstacles, refill and dump stops.
 * A congested supermarket floor loses far more time to manoeuvring than an
 * open warehouse aisle, so the factor is environment-dependent.
 */
class MachineSelectorService
{
    /** Real-world share of the rated productivity, by environment slug. */
    private const EFFICIENCY = [
        'warehouse' => 0.80,
        'factory' => 0.75,
        'airport' => 0.75,
        'parking' => 0.80,
        'shopping-mall' => 0.65,
        'hospital' => 0.60,
        'hotel' => 0.60,
        'car-wash' => 0.70,
    ];

    private const DEFAULT_EFFICIENCY = 0.70;

    /**
     * Machine classes ordered from smallest to largest. `max_area` is the
     * largest single-shift area the class comfortably covers.
     */
    private const CLASSES = [
        [
            'key' => 'manual',
            'max_area' => 400,
            'operator_type' => 'handheld',
            'category' => 'floor-polisher',
        ],
        [
            'key' => 'walk_behind_compact',
            'max_area' => 2000,
            'operator_type' => 'walk_behind',
            'category' => 'scrubber-dryer',
        ],
        [
            'key' => 'walk_behind_large',
            'max_area' => 5000,
            'operator_type' => 'walk_behind',
            'category' => 'scrubber-dryer',
        ],
        [
            'key' => 'ride_on',
            'max_area' => 20000,
            'operator_type' => 'ride_on',
            'category' => 'scrubber-dryer',
        ],
        [
            'key' => 'ride_on_heavy',
            'max_area' => PHP_INT_MAX,
            'operator_type' => 'ride_on',
            'category' => 'industrial-sweeper',
        ],
    ];

    /**
     * @return array{
     *     area: int,
     *     shift_hours: int,
     *     efficiency: float,
     *     required_productivity: int,
     *     class: array,
     *     estimated_hours: float|null,
     *     recommendations: Collection<int, Product>,
     *     alternatives: Collection<int, Product>
     * }
     */
    public function recommend(
        int $area,
        int $shiftHours = 8,
        ?Environment $environment = null,
        bool $rentalOnly = false,
    ): array {
        $area = max(1, $area);
        $shiftHours = max(1, min($shiftHours, 24));

        $efficiency = $environment
            ? (self::EFFICIENCY[$environment->slug] ?? self::DEFAULT_EFFICIENCY)
            : self::DEFAULT_EFFICIENCY;

        $required = (int) ceil($area / $shiftHours / $efficiency);
        $class = $this->classifyByArea($area);

        $recommendations = $this->queryFor($required, $class, $environment, $rentalOnly)->limit(4)->get();

        // If the exact class has nothing to show, widen to any machine that can
        // still finish the job rather than returning an empty state.
        $alternatives = $recommendations->isEmpty()
            ? $this->queryFor($required, null, $environment, $rentalOnly)->limit(4)->get()
            : $this->queryFor((int) round($required * 0.6), null, $environment, $rentalOnly)
                ->whereNotIn('products.id', $recommendations->modelKeys())
                ->limit(3)
                ->get();

        $best = $recommendations->first() ?? $alternatives->first();

        return [
            'area' => $area,
            'shift_hours' => $shiftHours,
            'efficiency' => $efficiency,
            'required_productivity' => $required,
            'class' => $class,
            'estimated_hours' => $best?->productivity_sqm_h
                ? round($area / ($best->productivity_sqm_h * $efficiency), 1)
                : null,
            'recommendations' => $recommendations,
            'alternatives' => $alternatives,
        ];
    }

    /** @return array{key: string, operator_type: string, category: string} */
    public function classifyByArea(int $area): array
    {
        foreach (self::CLASSES as $class) {
            if ($area <= $class['max_area']) {
                return $class;
            }
        }

        return self::CLASSES[array_key_last(self::CLASSES)];
    }

    private function queryFor(
        int $requiredProductivity,
        ?array $class,
        ?Environment $environment,
        bool $rentalOnly,
    ): Builder {
        return Product::query()
            ->active()
            ->with(['brand', 'category'])
            ->where('productivity_sqm_h', '>=', $requiredProductivity)
            ->when($class, fn (Builder $q) => $q->where('operator_type', $class['operator_type']))
            ->when($rentalOnly, fn (Builder $q) => $q->rentable())
            ->when($environment, fn (Builder $q) => $q->whereHas(
                'environments',
                fn (Builder $e) => $e->where('environments.id', $environment->id)
            ))
            // Smallest machine that still does the job — over-specifying is the
            // most common and most expensive mistake in this category.
            ->orderBy('productivity_sqm_h');
    }
}
