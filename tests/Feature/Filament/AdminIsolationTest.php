<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
});

it('shows a tenant admin only their own tenant tickets in the panel', function (): void {
    $tenantA = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);

    $tenantA->makeCurrent();
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $adminA->assignRole(Role::TenantAdmin->value);
    $customerA = User::factory()->create(['tenant_id' => $tenantA->id]);
    app(CreateTicket::class)(new CreateTicketDTO('Alpha issue', 'body', $customerA->id));

    $tenantB->makeCurrent();
    $customerB = User::factory()->create(['tenant_id' => $tenantB->id]);
    app(CreateTicket::class)(new CreateTicketDTO('Bravo issue', 'body', $customerB->id));

    $this->actingAs($adminA)->get('/admin/tickets')
        ->assertOk()
        ->assertSee('Alpha issue')
        ->assertDontSee('Bravo issue');
});

it('shows a tenant admin only their own tenant agents in the panel', function (): void {
    $tenantA = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);

    $tenantA->makeCurrent();
    $adminA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $adminA->assignRole(Role::TenantAdmin->value);
    $alice = User::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'Alice Agent']);
    $alice->assignRole(Role::Agent->value);

    $tenantB->makeCurrent();
    $bob = User::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Bob Agent']);
    $bob->assignRole(Role::Agent->value);

    $this->actingAs($adminA)->get('/admin/agents')
        ->assertOk()
        ->assertSee('Alice Agent')
        ->assertDontSee('Bob Agent');
});
