<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TenantServiceContract;
use App\DTOs\CreateTenantDTO;
use App\Models\Tenant;

final class TenantService implements TenantServiceContract
{
    public function create(CreateTenantDTO $data): Tenant
    {
        return Tenant::create([
            'name' => $data->name,
            'domain' => $data->domain,
            'plan' => $data->plan,
            'status' => $data->status,
        ]);
    }
}
