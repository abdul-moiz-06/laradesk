<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Row-Level Security for the audit trail. The tenant_isolation policies fence
 * every read and write to the current tenant (the `app.current_tenant` session
 * variable). There is deliberately NO update or delete policy, so with FORCE
 * ROW LEVEL SECURITY the application role can only append and read: the audit
 * log is tamper-evident at the database, not just in the UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE activity_log ENABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE activity_log FORCE ROW LEVEL SECURITY');
        DB::statement("
            CREATE POLICY tenant_select ON activity_log
            FOR SELECT
            USING (tenant_id = NULLIF(current_setting('app.current_tenant', true), '')::bigint)
        ");
        DB::statement("
            CREATE POLICY tenant_insert ON activity_log
            FOR INSERT
            WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant', true), '')::bigint)
        ");
    }

    public function down(): void
    {
        DB::statement('DROP POLICY IF EXISTS tenant_select ON activity_log');
        DB::statement('DROP POLICY IF EXISTS tenant_insert ON activity_log');
        DB::statement('ALTER TABLE activity_log NO FORCE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE activity_log DISABLE ROW LEVEL SECURITY');
    }
};
