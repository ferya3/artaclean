<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewLeadCaptured;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

/**
 * Queued so a visitor never waits on mail delivery to see the thank-you
 * screen. Horizon runs this on the `notifications` queue.
 */
class NotifySalesOfNewLead implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $leadId)
    {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $lead = Lead::with('product')->find($this->leadId);

        if ($lead === null) {
            return;
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', [Role::ADMIN, Role::SALES]))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new NewLeadCaptured($lead));
    }
}
