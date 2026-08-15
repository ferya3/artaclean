<?php

declare(strict_types=1);

namespace App\CQRS\Commands;

use App\CQRS\Command;
use App\Enums\RentalPeriod;

/**
 * "استعلام قیمت روز" and the rental enquiry both land here — a rental request
 * is a quote with a period attached, not a separate pipeline.
 */
final readonly class SubmitQuoteRequest implements Command
{
    public function __construct(
        public CaptureLead $lead,
        public ?int $productId = null,
        public int $quantity = 1,
        public string $type = 'purchase',
        public ?RentalPeriod $rentalPeriod = null,
        public ?int $rentalDuration = null,
        public ?string $rentalStartsAt = null,
        public ?string $customerNote = null,
    ) {}
}
