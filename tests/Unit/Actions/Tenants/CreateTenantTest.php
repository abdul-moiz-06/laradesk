<?php

declare(strict_types=1);

use App\Actions\Tenants\CreateTenant;
use App\Contracts\TenantServiceContract;
use App\DTOs\CreateTenantDTO;
use App\Models\Tenant;

it('delegates tenant creation to the tenant service', function (): void {
    $dto = new CreateTenantDTO(name: 'Acme', domain: 'acme.laradesk.test');
    $tenant = new Tenant;

    $service = Mockery::mock(TenantServiceContract::class);
    $service->shouldReceive('create')->once()->with($dto)->andReturn($tenant);

    $action = new CreateTenant($service);

    expect($action($dto))->toBe($tenant);
});
