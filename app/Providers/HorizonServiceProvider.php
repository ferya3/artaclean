<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::night();
    }

    /**
     * Horizon exposes queue payloads, so it is restricted to administrators
     * rather than left on the default local-only gate.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user) {
            return $user?->is_active && $user->hasRole(Role::ADMIN);
        });
    }
}
