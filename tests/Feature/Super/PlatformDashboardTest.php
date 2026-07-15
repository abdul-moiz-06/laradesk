<?php

declare(strict_types=1);

use App\Contracts\PlatformMetricsContract;
use App\DTOs\CompanyMetricsDTO;
use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Filament\Super\Widgets\PlatformCompaniesTable;
use App\Filament\Super\Widgets\PlatformStatsOverview;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed([RoleSeeder::class, PermissionSeeder::class]));

it('shows the platform widgets only to a super admin', function (): void {
    $super = User::factory()->create(['tenant_id' => null]);
    $super->assignRole(Role::SuperAdmin->value);

    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $admin->assignRole(Role::TenantAdmin->value);

    $this->actingAs($super);
    expect(PlatformStatsOverview::canView())->toBeTrue();

    $this->actingAs($admin);
    expect(PlatformStatsOverview::canView())->toBeFalse();
});

it('feeds the companies widget from the gated metrics service', function (): void {
    $this->mock(PlatformMetricsContract::class, function ($mock): void {
        $mock->shouldReceive('perCompany')->andReturn([
            new CompanyMetricsDTO(
                id: 1,
                name: 'Zeta Corp',
                plan: 'pro',
                status: TenantStatus::Active,
                usersCount: 4,
                ticketsCount: 8,
                openTicketsCount: 3,
                createdAt: CarbonImmutable::parse('2026-01-15'),
            ),
        ]);
    });

    $companies = (new PlatformCompaniesTable)->getCompanies();

    expect($companies)->toHaveCount(1)
        ->and($companies[0]->name)->toBe('Zeta Corp')
        ->and($companies[0]->ticketsCount)->toBe(8);
});
