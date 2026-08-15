<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Enums\RentalPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'rental_period' => RentalPeriod::class,
            'quoted_price' => 'decimal:0',
            'discount' => 'decimal:0',
            'valid_until' => 'date',
            'rental_starts_at' => 'date',
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $quote) {
            $quote->reference ??= 'QT-'.strtoupper(Str::random(8));
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function scopeAwaitingResponse(Builder $query): Builder
    {
        return $query->whereIn('status', [QuoteStatus::Pending->value, QuoteStatus::InReview->value]);
    }

    public function netPrice(): ?float
    {
        return $this->quoted_price === null
            ? null
            : max(0, (float) $this->quoted_price - (float) $this->discount);
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }
}
