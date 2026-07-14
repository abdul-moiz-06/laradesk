<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant from the authenticated API token's user, rather
 * than the request host. The token is bound to one user who belongs to exactly
 * one tenant, so the tenant context can never be spoofed or mismatched.
 */
final class SetTenantFromToken
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
