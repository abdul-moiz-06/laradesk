<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

/**
 * The audit trail is read-only and append-only: it may be viewed by staff with
 * the audit permission (TenantAdmin), and never created, edited, or deleted
 * through the application. No create/update/delete methods means those abilities
 * default-deny.
 */
final class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit.viewAny');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->can('audit.viewAny');
    }
}
