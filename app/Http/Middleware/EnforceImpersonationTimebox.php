<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Impersonation\StopImpersonation;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Automatically ends an impersonation session once its time box has passed,
 * returning the SuperAdmin to the platform panel. Runs on every admin-panel
 * request; a no-op for ordinary, non-impersonated sessions.
 */
final class EnforceImpersonationTimebox
{
    public function __construct(
        private readonly StopImpersonation $stop,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expiresAt = session()->get('impersonation_expires_at');

        if (is_string($expiresAt) && CarbonImmutable::parse($expiresAt)->isPast()) {
            ($this->stop)();

            return redirect()->to('/super');
        }

        return $next($request);
    }
}
