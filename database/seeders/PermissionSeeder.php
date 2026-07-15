<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const array TICKET_PERMISSIONS = [
        'ticket.viewAny',
        'ticket.view',
        'ticket.reply',
        'ticket.assign',
        'ticket.close',
    ];

    /**
     * @var list<string>
     */
    private const array AGENT_PERMISSIONS = [
        'agent.viewAny',
        'agent.view',
        'agent.create',
        'agent.update',
        'agent.delete',
    ];

    /**
     * @var list<string>
     */
    private const array AUDIT_PERMISSIONS = [
        'audit.viewAny',
    ];

    public function run(): void
    {
        foreach ([...self::TICKET_PERMISSIONS, ...self::AGENT_PERMISSIONS, ...self::AUDIT_PERMISSIONS] as $name) {
            Permission::findOrCreate($name);
        }

        // Refresh the cache so the freshly-created permissions resolve by name.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // TenantAdmin runs the whole company desk (incl. the audit trail);
        // Agents work tickets only.
        Role::findByName(RoleEnum::TenantAdmin->value)
            ->givePermissionTo([...self::TICKET_PERMISSIONS, ...self::AGENT_PERMISSIONS, ...self::AUDIT_PERMISSIONS]);

        Role::findByName(RoleEnum::Agent->value)
            ->givePermissionTo(self::TICKET_PERMISSIONS);
    }
}
