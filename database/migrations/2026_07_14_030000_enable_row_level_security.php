<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL Row-Level Security backstop for tenant isolation.
 *
 * Each tenant-scoped table only exposes rows whose tenant_id matches the
 * `app.current_tenant` session variable, which is set from the authenticated
 * user's tenant on every request. FORCE makes the policy apply even to the
 * table owner (the application role), so a forgotten Eloquent scope or a raw
 * query still cannot cross tenants. When the variable is unset the policy
 * matches no rows — isolation fails closed.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = ['tickets', 'ticket_replies'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement("
                CREATE POLICY tenant_isolation ON {$table}
                USING (tenant_id = NULLIF(current_setting('app.current_tenant', true), '')::bigint)
                WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant', true), '')::bigint)
            ");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP POLICY IF EXISTS tenant_isolation ON {$table}");
            DB::statement("ALTER TABLE {$table} NO FORCE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
