<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AIServiceContract;
use App\Contracts\PlatformMetricsContract;
use App\Contracts\TenantServiceContract;
use App\Services\AIService;
use App\Services\PlatformMetricsService;
use App\Services\TenantService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TenantServiceContract::class, TenantService::class);
        $this->app->bind(AIServiceContract::class, AIService::class);
        $this->app->bind(PlatformMetricsContract::class, PlatformMetricsService::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Date::use(CarbonImmutable::class);

        RateLimiter::for('api', function (Request $request): Limit {
            $identifier = $request->user()?->getAuthIdentifier();
            $key = (is_int($identifier) || is_string($identifier))
                ? 'user:'.$identifier
                : 'ip:'.($request->ip() ?? 'unknown');

            return Limit::perMinute(60)->by($key);
        });

        // Throttle magic-link requests by both IP and target email, so the
        // portal sign-in cannot be used to spray links or probe accounts.
        RateLimiter::for('portal-login', function (Request $request): array {
            $email = $request->string('email')->lower()->toString();

            return [
                Limit::perMinute(5)->by('ip:'.($request->ip() ?? 'unknown')),
                Limit::perMinute(3)->by('email:'.$email),
            ];
        });
    }
}
