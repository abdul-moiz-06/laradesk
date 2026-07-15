<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Governs agent management in the admin panel. Only a TenantAdmin (via the
 * agent.* permissions) may manage staff, and only within their own tenant.
 * Viewing/updating oneself is always allowed so the panel account page works.
 */
final class UserPolicy
{
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
