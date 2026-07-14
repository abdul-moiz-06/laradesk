<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the current tenant from the authenticated admin-panel user, so both the
 * Eloquent global scope and PostgreSQL Row-Level Security apply to every panel
 * query. Like the API, the tenant is taken from the signed-in identity — never
 * the host. Fails closed: a user without a resolvable tenant is denied.
 */
final class SetTenantForPanel
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->tenant_id === null) {
            abort(403, 'This account is not associated with a tenant.');
        }

        $tenant = Tenant::find($user->tenant_id);

        if ($tenant === null) {
            abort(403, 'The associated tenant could not be found.');
        }

        $tenant->makeCurrent();

        return $next($request);
    }
}
