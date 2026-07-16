<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
});

it('lets a super admin view the horizon dashboard', function (): void {
    $super = User::factory()->create(['tenant_id' => null]);
    $super->assignRole(Role::SuperAdmin->value);

    expect(Gate::forUser($super)->allows('viewHorizon'))->toBeTrue();
});

it('denies a tenant admin the horizon dashboard', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $admin->assignRole(Role::TenantAdmin->value);

    expect(Gate::forUser($admin)->allows('viewHorizon'))->toBeFalse();
});

it('denies a guest the horizon dashboard', function (): void {
    expect(Gate::allows('viewHorizon'))->toBeFalse();
});
