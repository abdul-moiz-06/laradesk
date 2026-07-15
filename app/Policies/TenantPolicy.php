<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;

/**
 * Companies are managed only from the platform panel, only by a SuperAdmin.
 * There is deliberately no delete: a company is suspended, never erased.
 */
final class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }
}
