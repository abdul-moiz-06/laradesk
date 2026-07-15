<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Login;

final class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || $user->tenant_id === null) {
            return;
        }

        // Login fires before the panel's tenant middleware, so establish the
        // tenant from the just-authenticated user before writing the audit.
        Tenant::find($user->tenant_id)?->makeCurrent();

        activity()
            ->causedBy($user)
            ->event('Login')
            ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
            ->log('Signed in');
    }
}
