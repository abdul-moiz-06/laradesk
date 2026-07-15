<?php

declare(strict_types=1);

use App\Actions\Tenants\ActivateTenant;
use App\Actions\Tenants\CreateTenantAdmin;
use App\Actions\Tenants\SuspendTenant;
use App\DTOs\CreateTenantAdminDTO;
use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Models\Activity;
use App\Models\Tenant;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

beforeEach(fn () => $this->seed([RoleSeeder::class, PermissionSeeder::class]));

it('suspends a company and records it in that company audit trail', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);

    app(SuspendTenant::class)($tenant);

    expect($tenant->fresh()->status)->toBe(TenantStatus::Suspended);

    $tenant->makeCurrent();
    expect(Activity::where('event', 'TenantSuspended')->exists())->toBeTrue();
});

it('activates a suspended company', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'suspended']);

    app(ActivateTenant::class)($tenant);

    expect($tenant->fresh()->status)->toBe(TenantStatus::Active);

    $tenant->makeCurrent();
    expect(Activity::where('event', 'TenantActivated')->exists())->toBeTrue();
});

it('creates a tenant admin for a company and invites them', function (): void {
    Notification::fake();
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);

    $admin = app(CreateTenantAdmin::class)(new CreateTenantAdminDTO($tenant->id, 'Ann Admin', 'ann@acme.test'));

    expect($admin->tenant_id)->toBe($tenant->id)
        ->and($admin->hasRole(Role::TenantAdmin->value))->toBeTrue()
        ->and($admin->email)->toBe('ann@acme.test');

    Notification::assertSentTo($admin, ResetPassword::class);

    $tenant->makeCurrent();
    expect(Activity::where('event', 'TenantAdminInvited')->exists())->toBeTrue();
});
