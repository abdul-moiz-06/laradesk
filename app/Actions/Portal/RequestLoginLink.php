<?php

declare(strict_types=1);

namespace App\Actions\Portal;

use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\CustomerLoginLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * Emails a customer a passwordless, single-use, expiring sign-in link.
 *
 * Deliberately a no-op (no error, no different response) when the email does
 * not belong to a customer of a live company, so the caller can always return
 * the same message and never leak whether an account exists.
 */
final class RequestLoginLink
{
    public const string CACHE_PREFIX = 'portal:login:';

    public function __invoke(string $email): void
    {
        $customer = User::query()
            ->where('email', Str::lower(trim($email)))
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', Role::Customer->value))
            ->first();

        if ($customer === null || $customer->tenant_id === null) {
            return;
        }

        $tenant = Tenant::find($customer->tenant_id);

        if ($tenant === null || $tenant->status === TenantStatus::Suspended) {
            return;
        }

        $token = Str::random(64);

        Cache::put(
            self::CACHE_PREFIX.$token,
            $customer->id,
            now()->addMinutes(Config::integer('portal.login_link_minutes')),
        );

        // The link is requested anonymously (no tenant is current yet), but the
        // queued mail is tenant-aware, so set the customer's company as current
        // for the worker to re-establish. Done after caching the token, which
        // is looked up anonymously and must stay unprefixed.
        $tenant->makeCurrent();

        $customer->notify(new CustomerLoginLink($token));
    }
}
