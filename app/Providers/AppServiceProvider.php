<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AIServiceContract;
use App\Contracts\TenantServiceContract;
use App\Services\AIService;
use App\Services\TenantService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
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
    }
}
