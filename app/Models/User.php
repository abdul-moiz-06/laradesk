<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Enums\TenantStatus;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $tenant_id
 * @property string|null $app_authentication_secret
 * @property array<int, string>|null $app_authentication_recovery_codes
 * @property-read Tenant|null $tenant
 */
#[Fillable(['name', 'email', 'password', 'tenant_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Only tenant staff (a TenantAdmin or Agent) belonging to a tenant may enter
     * the admin panel. The tenant itself is bound from this user by middleware.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // The platform panel is for the SuperAdmin, who belongs to no company.
        if ($panel->getId() === 'super') {
            return $this->hasRole(Role::SuperAdmin->value);
        }

        // The tenant panel is for a company's staff, and a suspended company's
        // staff are locked out entirely.
        return $this->tenant_id !== null
            && $this->hasAnyRole([Role::TenantAdmin->value, Role::Agent->value])
            && ! $this->belongsToSuspendedTenant();
    }

    public function belongsToSuspendedTenant(): bool
    {
        return Tenant::query()
            ->whereKey($this->tenant_id)
            ->where('status', TenantStatus::Suspended->value)
            ->exists();
    }

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(#[\SensitiveParameter] ?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /**
     * @return array<int, string>|null
     */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    /**
     * @param  array<int, string>|null  $codes
     */
    public function saveAppAuthenticationRecoveryCodes(#[\SensitiveParameter] ?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Normalise e-mail addresses to a trimmed, lower-cased form on write, so
     * PostgreSQL's case-sensitive comparisons treat "Jane@X" and "jane@x" as
     * one address for both uniqueness and login.
     *
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::lower(trim($value)),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }
}
