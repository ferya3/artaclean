<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'subtotal' => 'decimal:0',
            'discount' => 'decimal:0',
            'tax' => 'decimal:0',
            'shipping' => 'decimal:0',
            'total' => 'decimal:0',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->reference ??= 'OR-'.strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculate(): void
    {
        $subtotal = (float) $this->items()->sum('total');

        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - (float) $this->discount + (float) $this->tax + (float) $this->shipping),
        ])->save();
    }
}
