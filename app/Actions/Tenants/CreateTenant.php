<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Contracts\TenantServiceContract;
use App\DTOs\CreateTenantDTO;
use App\Models\Tenant;

final readonly class CreateTenant
{
    public function __construct(
        private TenantServiceContract $tenants,
    ) {}

    public function __invoke(CreateTenantDTO $data): Tenant
    {
        return $this->tenants->create($data);
    }
}
