<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Enums\TenantStatus;
use App\Events\TenantActivated;
use App\Models\Tenant;

final class ActivateTenant
{
    public function __invoke(Tenant $tenant): Tenant
    {
        $tenant->status = TenantStatus::Active;
        $tenant->save();

        // Record the action in the affected company's own audit trail.
        $tenant->makeCurrent();
        TenantActivated::dispatch($tenant);

        return $tenant;
    }
}
