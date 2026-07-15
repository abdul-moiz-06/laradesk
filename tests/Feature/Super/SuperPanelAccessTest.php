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

function superAdmin(): User
{
    $user = User::factory()->create(['tenant_id' => null]);
    $user->assignRole(Role::SuperAdmin->value);

    return withMfa($user);
}

it('lets a super admin into the platform panel', function (): void {
    $this->actingAs(superAdmin())->get('/super')->assertOk();
});

it('forbids a tenant admin from the platform panel', function (): void {
    $admin = withMfa(User::factory()->create(['tenant_id' => $this->tenant->id]));
    $admin->assignRole(Role::TenantAdmin->value);

    $this->actingAs($admin)->get('/super')->assertForbidden();
});

it('forbids a super admin from the tenant panel', function (): void {
    $this->actingAs(superAdmin())->get('/admin')->assertForbidden();
});
