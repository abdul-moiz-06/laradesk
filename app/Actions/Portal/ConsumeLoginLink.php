<?php

declare(strict_types=1);

namespace App\Actions\Portal;

use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Consumes a magic sign-in token and signs the customer in on the customer
 * guard. Single-use: the token is pulled (read and deleted) atomically, so a
 * link cannot be replayed. Returns the customer on success, null otherwise.
 */
final class ConsumeLoginLink
{
    public function __invoke(string $token): ?User
    {
        // Redis returns numeric cache values as strings while the array store
        // (used in tests) keeps them as ints, so accept either.
        $customerId = Cache::pull(RequestLoginLink::CACHE_PREFIX.$token);

        if (! is_numeric($customerId)) {
            return null;
        }

        $customer = User::find((int) $customerId);

        if ($customer === null
            || $customer->tenant_id === null
            || ! $customer->hasRole(Role::Customer->value)) {
            return null;
        }

        $tenant = Tenant::find($customer->tenant_id);

        if ($tenant === null || $tenant->status === TenantStatus::Suspended) {
            return null;
        }

        Auth::guard('customer')->login($customer);

        return $customer;
    }
}
