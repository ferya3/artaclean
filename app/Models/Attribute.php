<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_filterable' => 'boolean',
            'is_comparable' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
