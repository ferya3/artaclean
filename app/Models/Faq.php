<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Faq extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected array $translatable = ['question', 'answer'];

    protected function casts(): array
    {
        return [
            'question' => 'array',
            'answer' => 'array',
            'is_active' => 'boolean',
            'in_schema' => 'boolean',
        ];
    }

    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** FAQs with no owner form the site-wide FAQ page. */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('faqable_type');
    }
}
