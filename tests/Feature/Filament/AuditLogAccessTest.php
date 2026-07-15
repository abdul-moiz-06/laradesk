<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $this->tenant->makeCurrent();
});

it('lets a tenant admin open the audit log', function (): void {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole(Role::TenantAdmin->value);

    $this->actingAs(withMfa($admin))->get('/admin/activities')->assertOk();
});

it('forbids an agent from the audit log', function (): void {
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $agent->assignRole(Role::Agent->value);

    $this->actingAs(withMfa($agent))->get('/admin/activities')->assertForbidden();
});
