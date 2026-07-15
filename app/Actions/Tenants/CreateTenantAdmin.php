<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\DTOs\CreateTenantAdminDTO;
use App\Enums\Role;
use App\Events\TenantAdminInvited;
use App\Models\Tenant;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Creates the first (or an additional) TenantAdmin for a company from the
 * SuperAdmin panel: the role is fixed, the tenant is the selected company, and
 * the admin is invited to set their own password via the tenant panel's reset
 * link. No plaintext password is handled.
 */
final class CreateTenantAdmin
{
    public function __construct(private readonly Hasher $hasher) {}

    public function __invoke(CreateTenantAdminDTO $data): User
    {
        $tenant = Tenant::findOrFail($data->tenantId);

        $admin = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $this->hasher->make(Str::random(64)),
            'tenant_id' => $tenant->getKey(),
        ]);

        $admin->assignRole(Role::TenantAdmin->value);

        // The invite links to the tenant (/admin) panel, not the platform panel.
        $token = Password::createToken($admin);
        $notification = new ResetPassword($token);
        $notification->url = Filament::getPanel('admin')->getResetPasswordUrl($token, $admin);
        $admin->notify($notification);

        $tenant->makeCurrent();
        TenantAdminInvited::dispatch($tenant, $admin);

        return $admin;
    }
}
