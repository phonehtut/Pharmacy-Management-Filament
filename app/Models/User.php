<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'branch_id', 'role', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'transferred_by');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $normalizedRole = strtolower((string) $this->role);

        return match ($panel->getId()) {
            'admin' => $normalizedRole === 'admin',
            'cashier' => $normalizedRole === 'cashier',
            'pharmacist' => $normalizedRole === 'pharmacist',
            default => false,
        };
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Branch) {
            return false;
        }

        return (int) $tenant->getKey() === (int) $this->branch_id;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if (! in_array($panel->getId(), ['cashier', 'pharmacist'], true)) {
            return collect();
        }

        if (! $this->branch_id) {
            return collect();
        }

        return Branch::query()
            ->whereKey($this->branch_id)
            ->get();
    }
}
