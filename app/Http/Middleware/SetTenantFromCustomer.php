<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant from the authenticated customer (the `customer`
 * guard), not the request host, exactly like the API and the panels resolve it
 * from the signed-in identity. So the Eloquent scope and PostgreSQL Row-Level
 * Security fence every portal query to that customer's one company. Only a
 * Customer of a live company may pass; it fails closed otherwise.
 */
final class SetTenantFromCustomer
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('customer');

        if (! $user instanceof User
            || $user->tenant_id === null
            || ! $user->hasRole(Role::Customer->value)) {
            abort(403, 'This account may not use the support portal.');
        }

        $tenant = Tenant::find($user->tenant_id);

        if ($tenant === null) {
            abort(403, 'The associated company could not be found.');
        }

        if ($tenant->status === TenantStatus::Suspended) {
            abort(403, 'This company is not currently accepting support requests.');
        }

        $tenant->makeCurrent();

        return $next($request);
    }
}
