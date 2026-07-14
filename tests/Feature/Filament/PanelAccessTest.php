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

function panelUser(string $role): User
{
    /** @var Tenant $tenant */
    $tenant = test()->tenant;
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $user->assignRole($role);

    return $user;
}

it('lets a tenant admin into the admin panel', function (): void {
    $this->actingAs(panelUser(Role::TenantAdmin->value))->get('/admin')->assertOk();
});

it('lets an agent into the admin panel', function (): void {
    $this->actingAs(panelUser(Role::Agent->value))->get('/admin')->assertOk();
});

it('forbids a customer from the admin panel', function (): void {
    $this->actingAs(panelUser(Role::Customer->value))->get('/admin')->assertForbidden();
});

it('forbids a user with no tenant from the admin panel', function (): void {
    $user = panelUser(Role::TenantAdmin->value);
    $user->update(['tenant_id' => null]);

    $this->actingAs($user->fresh())->get('/admin')->assertForbidden();
});
