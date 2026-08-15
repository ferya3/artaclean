<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'company', 'city', 'avatar',
        'is_active', 'dealer_id', 'locale',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasRole(string ...$slugs): bool
    {
        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Staff reach the Filament panel; dealers get the separate dealer panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasRole(Role::ADMIN, Role::SALES, Role::EDITOR),
            'dealer' => $this->hasRole(Role::DEALER) && $this->dealer_id !== null,
            default => false,
        };
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
