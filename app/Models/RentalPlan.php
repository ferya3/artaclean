<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RentalPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'period' => RentalPeriod::class,
            'price' => 'decimal:0',
            'deposit' => 'decimal:0',
            'operator_included' => 'boolean',
            'price_visible' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function showsPrice(): bool
    {
        return config('site.prices_public') && $this->price_visible && $this->price !== null;
    }
}
