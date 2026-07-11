<?php

declare(strict_types=1);

use App\Actions\Tenants\CreateTenant;
use App\DTOs\CreateTenantDTO;
use App\Enums\TenantStatus;

it('persists a new tenant', function (): void {
    $tenant = app(CreateTenant::class)(new CreateTenantDTO(
        name: 'Acme Inc',
        domain: 'acme.laradesk.test',
        status: TenantStatus::Active,
    ));

    expect($tenant->name)->toBe('Acme Inc')
        ->and($tenant->status)->toBe(TenantStatus::Active);

    $this->assertDatabaseHas('tenants', [
        'domain' => 'acme.laradesk.test',
        'status' => 'active',
    ]);
});
