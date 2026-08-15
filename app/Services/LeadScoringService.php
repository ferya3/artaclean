<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\Product;

/**
 * Ranks incoming leads 0-100 so the sales queue surfaces the buyer who asked
 * about a 300-million-toman ride-on scrubber before the student downloading a
 * catalog. Deliberately simple and inspectable — a sales manager should be
 * able to read the rules and agree with them.
 */
class LeadScoringService
{
    private const BUSINESS_WEIGHT = [
        'factory' => 25,
        'hospital' => 22,
        'shopping-mall' => 22,
        'airport' => 25,
        'hotel' => 18,
        'warehouse' => 20,
        'facility-services' => 20,
        'car-wash' => 15,
        'contractor' => 12,
        'other' => 5,
    ];

    public function score(Lead $lead): int
    {
        $score = $lead->source instanceof LeadSource ? $lead->source->baseScore() : 10;

        $score += self::BUSINESS_WEIGHT[$lead->business_type] ?? 0;

        if (filled($lead->company)) {
            $score += 10;
        }

        if (filled($lead->email)) {
            $score += 5;
        }

        // A named product means the buyer is past the browsing stage; a
        // high-ticket machine means the deal is worth chasing today.
        if ($lead->product_id) {
            $score += 10;

            $price = Product::query()->whereKey($lead->product_id)->value('price');

            if ($price !== null && (float) $price >= 1_000_000_000) {
                $score += 10;
            }
        }

        $area = (int) data_get($lead->meta, 'area', 0);

        $score += match (true) {
            $area >= 20000 => 15,
            $area >= 5000 => 10,
            $area >= 1000 => 5,
            default => 0,
        };

        return max(0, min(100, $score));
    }

    public function estimateValue(Lead $lead): ?float
    {
        if (! $lead->product_id) {
            return null;
        }

        $price = Product::query()->whereKey($lead->product_id)->value('price');

        return $price === null ? null : (float) $price;
    }
}
