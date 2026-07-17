<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
});

function customerFor(Tenant $tenant): User
{
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $customer->assignRole(Role::Customer->value);

    return $customer;
}

it('shows the public portal sign-in page', function (): void {
    $this->get('/portal/login')->assertOk()->assertSee('Sign in to support');
});

it('redirects a guest to the portal sign-in', function (): void {
    $this->get('/portal')->assertRedirect(route('portal.login'));
});

it('lets an authenticated customer into the portal', function (): void {
    $customer = customerFor($this->tenant);

    $this->actingAs($customer, 'customer')
        ->get('/portal')
        ->assertOk()
        ->assertSee('My tickets');

    expect(Tenant::current()?->getKey())->toBe($this->tenant->id);
});

it('blocks a customer whose company is suspended', function (): void {
    $this->tenant->update(['status' => 'suspended']);
    $customer = customerFor($this->tenant);

    $this->actingAs($customer, 'customer')->get('/portal')->assertForbidden();
});

it('blocks a non-customer from the portal', function (): void {
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $agent->assignRole(Role::Agent->value);

    $this->actingAs($agent, 'customer')->get('/portal')->assertForbidden();
});
