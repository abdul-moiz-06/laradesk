<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes the company bound from a signed support link the current tenant, so the
 * public submit flow (and the row-level rules underneath it) run scoped to that
 * one company. Runs after signature validation and route-model binding. A
 * suspended company is not accepting requests, so it fails closed.
 */
final class SetTenantFromSignedRoute
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        if ($tenant->status === TenantStatus::Suspended) {
            abort(403, 'This company is not currently accepting support requests.');
        }

        $tenant->makeCurrent();

        return $next($request);
    }
}
