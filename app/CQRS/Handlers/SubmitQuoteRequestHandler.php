<?php

declare(strict_types=1);

namespace App\CQRS\Handlers;

use App\CQRS\Bus\CommandBus;
use App\CQRS\Command;
use App\CQRS\Commands\SubmitQuoteRequest;
use App\CQRS\Handler;
use App\CQRS\Query;
use App\Models\Product;
use App\Models\Quote;

class SubmitQuoteRequestHandler implements Handler
{
    public function __construct(private readonly CommandBus $bus) {}

    public function handle(Command|Query $message): Quote
    {
        /** @var SubmitQuoteRequest $message */
        $lead = $this->bus->dispatch($message->lead);

        $quote = Quote::create([
            'lead_id' => $lead->id,
            'product_id' => $message->productId,
            'type' => $message->type,
            'quantity' => max(1, $message->quantity),
            'rental_period' => $message->rentalPeriod,
            'rental_duration' => $message->rentalDuration,
            'rental_starts_at' => $message->rentalStartsAt,
            'customer_note' => $message->customerNote,
            // Prices move weekly, so a quote is only good for a fortnight.
            'valid_until' => now()->addDays(14),
        ]);

        if ($message->productId) {
            Product::withoutTimestamps(
                fn () => Product::whereKey($message->productId)->increment('quotes_count')
            );
        }

        return $quote;
    }
}
