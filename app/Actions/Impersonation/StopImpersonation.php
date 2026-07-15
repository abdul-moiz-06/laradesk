<?php

declare(strict_types=1);

namespace App\Actions\Impersonation;

use App\Events\ImpersonationEnded;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

/**
 * Ends an impersonation session and restores the SuperAdmin. Idempotent: a
 * no-op when nothing is being impersonated, so it is safe to call from the
 * banner, the time-box middleware, or twice. The end is audited in the
 * affected company's trail once the SuperAdmin identity is back.
 */
final class StopImpersonation
{
    public function __invoke(): void
    {
        $impersonatorId = session('impersonator_id');

        if (! is_int($impersonatorId)) {
            return;
        }

        $impersonator = User::find($impersonatorId);
        $target = Auth::user();

        if (! $impersonator instanceof User) {
            return;
        }

        $tenant = $target instanceof User && $target->tenant_id !== null
            ? Tenant::find($target->tenant_id)
            : null;

        // Restore the SuperAdmin, then clear the impersonation markers so the
        // end is recorded as a plain SuperAdmin action, not an impersonated one.
        Auth::login($impersonator);
        $this->forgetSessionPasswordHash();
        session()->forget(['impersonator_id', 'impersonator_email', 'impersonation_expires_at']);

        if ($target instanceof User && $tenant !== null) {
            $tenant->makeCurrent();
            event(new ImpersonationEnded($target));
        }
    }

    private function forgetSessionPasswordHash(): void
    {
        session()->forget('password_hash_'.Config::string('auth.defaults.guard'));
    }
}
