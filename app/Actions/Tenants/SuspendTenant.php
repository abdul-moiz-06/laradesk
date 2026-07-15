<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Enums\TenantStatus;
use App\Events\TenantSuspended;
use App\Models\Tenant;

final class SuspendTenant
{
    public function __invoke(Tenant $tenant): Tenant
    {
        $tenant->status = TenantStatus::Suspended;
        $tenant->save();

        // Record the action in the affected company's own audit trail.
        $tenant->makeCurrent();
        TenantSuspended::dispatch($tenant);

        return $tenant;
    }
}
