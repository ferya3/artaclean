<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\LeadRepository;
use App\Models\Lead;
use App\Models\LeadActivity;

class EloquentLeadRepository implements LeadRepository
{
    public function create(array $attributes): Lead
    {
        return Lead::create($attributes);
    }

    public function findRecentOpenByPhone(string $phone, int $withinHours = 24): ?Lead
    {
        return Lead::query()
            ->open()
            ->where('phone', $phone)
            ->where('created_at', '>=', now()->subHours($withinHours))
            ->latest('id')
            ->first();
    }

    public function logActivity(Lead $lead, string $type, ?string $body = null, array $meta = []): void
    {
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'body' => $body,
            'meta' => $meta === [] ? null : $meta,
        ]);
    }
}
