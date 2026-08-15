<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpec extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['label', 'value'];

    protected function casts(): array
    {
        return [
            'label' => 'array',
            'value' => 'array',
            'is_highlighted' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayValue(): string
    {
        return trim($this->value.' '.($this->unit ?? ''));
    }
}
