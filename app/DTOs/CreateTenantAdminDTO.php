<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CreateTenantAdminDTO
{
    public function __construct(
        public int $tenantId,
        public string $name,
        public string $email,
    ) {}
}
