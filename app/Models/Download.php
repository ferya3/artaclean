<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DownloadType;
use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class Download extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'type' => DownloadType::class,
            'requires_lead' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Always routed through the app rather than linked straight at object
     * storage, so gated files can capture a lead and the counter stays honest.
     */
    public function url(): string
    {
        return route('download.show', $this);
    }

    public function humanSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        // maxPrecision, not precision: a 625 KB file should not read "625.0 KB".
        return Number::fileSize($this->file_size, maxPrecision: 1);
    }
}
