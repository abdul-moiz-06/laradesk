<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\CreateTenantDTO;
use App\Models\Tenant;

interface TenantServiceContract
{
    public function create(CreateTenantDTO $data): Tenant;
}
