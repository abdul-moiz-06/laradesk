<?php

declare(strict_types=1);

namespace App\Actions\Impersonation;

use App\Events\ImpersonationStarted;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Begins a break-glass impersonation session: the SuperAdmin becomes a company
 * user so support can reproduce and fix an issue in that company's context.
 *
 * The identity swap is deliberate: once the authenticated user is the target,
 * the panel's own tenant resolution binds the current tenant, so the Eloquent
 * scope and PostgreSQL Row-Level Security fence every query to that one company
 * with no special-casing. The session is time-boxed, and the start is audited
 * in the affected company's trail with the SuperAdmin named as the actor.
 */
final class StartImpersonation
{
    public function __invoke(User $target, string $reason): void
    {
        Gate::authorize('impersonate', $target);

        $impersonator = Auth::user();

        if (! $impersonator instanceof User || $target->tenant_id === null) {
            throw new RuntimeException('Impersonation requires an authenticated SuperAdmin and a company user.');
        }

        $tenant = Tenant::findOrFail($target->tenant_id);
        $expiresAt = CarbonImmutable::now()->addMinutes(Config::integer('impersonation.max_minutes'));

        // Record the start in the affected company's trail while the SuperAdmin
        // is still the acting identity, so they are the causer.
        $tenant->makeCurrent();
        event(new ImpersonationStarted($target, $reason, $expiresAt));

        // Become the target. Forgetting the session password hash lets the
        // panel's session guard re-seat it for the new user on the next request
        // instead of logging out on a hash mismatch.
        Auth::login($target);
        $this->forgetSessionPasswordHash();

        session()->put([
            'impersonator_id' => $impersonator->id,
            'impersonator_email' => $impersonator->email,
            'impersonation_expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    private function forgetSessionPasswordHash(): void
    {
        session()->forget('password_hash_'.Config::string('auth.defaults.guard'));
    }
}
