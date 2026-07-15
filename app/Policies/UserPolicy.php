<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

/**
 * Governs agent management in the admin panel. Only a TenantAdmin (via the
 * agent.* permissions) may manage staff, and only within their own tenant.
 * Viewing/updating oneself is always allowed so the panel account page works.
 */
final class UserPolicy
{
    /**
     * Only a SuperAdmin may impersonate, and only an onboarded company staff
     * member (a TenantAdmin or Agent who has completed two-factor set-up) whose
     * company is not suspended. Never oneself.
     */
    public function impersonate(User $user, User $target): bool
    {
        return $user->hasRole(Role::SuperAdmin->value)
            && $user->id !== $target->id
            && $target->tenant_id !== null
            && $target->hasAnyRole([Role::TenantAdmin->value, Role::Agent->value])
            && $target->app_authentication_secret !== null
            && ! $target->belongsToSuspendedTenant();
    }

    public function viewAny(User $user): bool
    {
        return $user->can('agent.viewAny');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id
            || ($user->can('agent.view') && $user->tenant_id === $model->tenant_id);
    }

    public function create(User $user): bool
    {
        return $user->can('agent.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id
            || ($user->can('agent.update') && $user->tenant_id === $model->tenant_id);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id
            && $user->can('agent.delete')
            && $user->tenant_id === $model->tenant_id;
    }
}
