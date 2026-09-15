<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PowerSource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * "چه دستگاهی نیاز دارم؟"
 *
 * A buyer does not arrive knowing they want a 70 litre walk-behind scrubber.
 * They arrive knowing the floor of their factory is 5,000 m² and there is oil
 * on it. This turns that sentence into a shortlist.
 *
 * Surface and soil are hard filters — a carpet extractor will never be the
 * right answer for an oily concrete floor, however good its figures are. Area
 * and frequency then rank what is left, and deliberately rank the *smallest
 * sufficient* machine first: overselling a machine is the most expensive
 * mistake in this trade, and the one this tool exists to prevent.
 */
class ProductAdvisor
{
    /**
     * Area bands, as a buyer estimates them. `typical` is the figure used to
     * size the machine — the top of the band, so the recommendation covers the
     * whole band rather than only its floor.
     */
    public const AREA_BANDS = [
        'under_500' => ['min' => 0, 'max' => 500, 'typical' => 500],
        '500_2000' => ['min' => 500, 'max' => 2000, 'typical' => 2000],
        '2000_10000' => ['min' => 2000, 'max' => 10000, 'typical' => 10000],
        'over_10000' => ['min' => 10000, 'max' => null, 'typical' => 20000],
    ];

    /** How often the machine runs, and what that implies for how it is built. */
    public const FREQUENCIES = ['daily', 'weekly', 'occasional'];

    /** Real-world share of rated productivity — turning, refilling, dumping. */
    private const EFFICIENCY = 0.70;

    private const SHIFT_HOURS = 8;

    /**
     * @return array{
     *     required_productivity: int,
     *     area: int,
     *     best: Product|null,
     *     alternatives: Collection<int, Product>,
     *     relaxed: bool,
     *     total: int
     * }
     */
    public function recommend(
        ?string $surface = null,
        ?string $soil = null,
        ?string $areaBand = null,
        ?string $frequency = null,
        bool $rentalOnly = false,
    ): array {
        $band = self::AREA_BANDS[$areaBand] ?? null;
        $area = $band['typical'] ?? 0;
        $required = $area > 0
            ? (int) ceil($area / self::SHIFT_HOURS / self::EFFICIENCY)
            : 0;

        $candidates = $this->candidates($surface, $soil, $rentalOnly);
        $relaxed = false;

        // Nothing matches both? Surface is the one that cannot bend, so the
        // soil filter is what gets dropped — and the caller is told, because a
        // recommendation that quietly ignored half the question is worse than
        // a short list.
        if ($candidates->isEmpty() && $soil !== null && $surface !== null) {
            $candidates = $this->candidates($surface, null, $rentalOnly);
            $relaxed = $candidates->isNotEmpty();
        }

        $ranked = $candidates
            ->sortByDesc(fn (Product $product) => $this->score($product, $required, $frequency))
            ->values();

        return [
            'required_productivity' => $required,
            'area' => $area,
            'best' => $ranked->first(),
            'alternatives' => $ranked->skip(1)->take(3)->values(),
            'relaxed' => $relaxed,
            'total' => $ranked->count(),
        ];
    }

    /** @return Collection<int, Product> */
    private function candidates(?string $surface, ?string $soil, bool $rentalOnly): Collection
    {
        return Product::query()
            ->active()
            ->with(['brand', 'category', 'environments'])
            ->when($surface, fn (Builder $q) => $q->forSurface($surface))
            ->when($soil, fn (Builder $q) => $q->forSoil($soil))
            ->when($rentalOnly, fn (Builder $q) => $q->rentable())
            // Consumables and parts answer a different question than "which
            // machine", so they never compete for the recommendation.
            ->whereHas('category', fn (Builder $q) => $q->whereNotIn('slug', ['spare-parts', 'cleaning-chemicals']))
            ->get();
    }

    /**
     * Higher is better. The shape that matters: meeting the required
     * productivity is worth a lot, and exceeding it by more than about
     * two-and-a-half times starts costing points again.
     */
    private function score(Product $product, int $required, ?string $frequency): float
    {
        $score = 0.0;
        $productivity = (int) $product->productivity_sqm_h;

        if ($required > 0 && $productivity > 0) {
            $ratio = $productivity / $required;

            $score += match (true) {
                $ratio < 0.6 => 0,       // nowhere near: cannot finish the shift
                $ratio < 1.0 => 18,      // tight, but a longer shift covers it
                $ratio <= 1.8 => 50,     // the right size
                $ratio <= 2.5 => 34,     // comfortable, some money left on the table
                default => 16,           // oversold
            };
        } elseif ($productivity > 0) {
            $score += 10;
        }

        $score += match ($frequency) {
            // Running every shift is what justifies a battery machine and what
            // punishes a trailing cable.
            'daily' => match ($product->power_source) {
                PowerSource::Battery => 16,
                PowerSource::Diesel, PowerSource::Petrol => 10,
                default => 4,
            },
            // Occasional use rarely earns the battery premium.
            'occasional' => match ($product->power_source) {
                PowerSource::Electric, PowerSource::Manual => 12,
                PowerSource::Battery => 4,
                default => 6,
            },
            default => 6,
        };

        if ($product->is_featured) {
            $score += 4;
        }

        if ($product->stock_status?->value === 'in_stock') {
            $score += 3;
        }

        return $score;
    }
}
