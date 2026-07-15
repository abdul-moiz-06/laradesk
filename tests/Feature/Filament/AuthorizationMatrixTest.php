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

dataset('permission matrix', [
    'tenant admin assigns tickets' => [Role::TenantAdmin, 'ticket.assign', true],
    'tenant admin closes tickets' => [Role::TenantAdmin, 'ticket.close', true],
    'tenant admin lists agents' => [Role::TenantAdmin, 'agent.viewAny', true],
    'tenant admin creates agents' => [Role::TenantAdmin, 'agent.create', true],
    'agent assigns tickets' => [Role::Agent, 'ticket.assign', true],
    'agent closes tickets' => [Role::Agent, 'ticket.close', true],
    'agent cannot list agents' => [Role::Agent, 'agent.viewAny', false],
    'agent cannot create agents' => [Role::Agent, 'agent.create', false],
    'customer cannot assign tickets' => [Role::Customer, 'ticket.assign', false],
    'customer cannot close tickets' => [Role::Customer, 'ticket.close', false],
    'customer cannot create agents' => [Role::Customer, 'agent.create', false],
]);

it('enforces the role and permission matrix', function (Role $role, string $ability, bool $allowed): void {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $user->assignRole($role->value);

    expect($user->can($ability))->toBe($allowed);
})->with('permission matrix');
