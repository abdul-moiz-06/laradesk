<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AIServiceContract;
use App\Contracts\TenantServiceContract;
use App\Services\AIService;
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
    }
}
