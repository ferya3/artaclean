<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'status' => LeadStatus::class,
            'meta' => 'array',
            'estimated_value' => 'decimal:0',
            'last_contacted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            $lead->reference ??= 'LD-'.strtoupper(Str::random(8));
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [LeadStatus::Won->value, LeadStatus::Lost->value]);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    /** Leads sitting in the pipeline untouched for longer than the SLA. */
    public function scopeStale(Builder $query, int $hours = 24): Builder
    {
        return $query->open()
            ->whereNull('last_contacted_at')
            ->where('created_at', '<=', now()->subHours($hours));
    }
}
