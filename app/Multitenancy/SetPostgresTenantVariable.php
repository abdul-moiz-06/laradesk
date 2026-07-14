<?php

declare(strict_types=1);

namespace App\Multitenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * Binds PostgreSQL's `app.current_tenant` session variable to the current
 * tenant. The Row-Level Security policies read this variable to fence every
 * query to the tenant's rows. Clearing it (no current tenant) fails closed.
 */
final class SetPostgresTenantVariable implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        $key = $tenant instanceof Model ? $tenant->getKey() : null;

        $this->setCurrentTenant(is_scalar($key) ? (string) $key : '');
    }

    public function forgetCurrent(): void
    {
        $this->setCurrentTenant('');
    }

    private function setCurrentTenant(string $tenantId): void
    {
        DB::statement('select set_config(?, ?, false)', ['app.current_tenant', $tenantId]);
    }
}
