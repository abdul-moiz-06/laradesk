<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * The Horizon queue dashboard is platform operations, so only the SuperAdmin
     * may view it. Their session is already two-factor verified through the
     * /super panel sign-in.
     */
    protected function gate(): void
    {
        Gate::define(
            'viewHorizon',
            fn (?User $user): bool => $user instanceof User && $user->hasRole(Role::SuperAdmin->value),
        );
    }
}
