<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Lead;

interface LeadRepository
{
    public function create(array $attributes): Lead;

    /**
     * The same buyer usually asks about three machines in one sitting. Reuse
     * an open lead from the same phone number instead of fragmenting the
     * pipeline into near-duplicate rows.
     */
    public function findRecentOpenByPhone(string $phone, int $withinHours = 24): ?Lead;

    public function logActivity(Lead $lead, string $type, ?string $body = null, array $meta = []): void;
}
