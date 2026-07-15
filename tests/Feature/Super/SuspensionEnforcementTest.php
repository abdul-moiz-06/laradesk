<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'suspended']);
    $this->tenant->makeCurrent();
});

it('locks a suspended company staff out of the admin panel', function (): void {
    $admin = withMfa(User::factory()->create(['tenant_id' => $this->tenant->id]));
    $admin->assignRole(Role::TenantAdmin->value);

    $this->actingAs($admin)->get('/admin')->assertForbidden();
});

it('rejects a suspended company on the API', function (): void {
    $customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
    Sanctum::actingAs($customer);

    $this->getJson('/api/v1/tickets')->assertForbidden();
});
