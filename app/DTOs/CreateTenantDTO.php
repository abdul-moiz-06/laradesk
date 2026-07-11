<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\TenantStatus;

final readonly class CreateTenantDTO
{
    public function __construct(
        public string $name,
        public string $domain,
        public ?string $plan = null,
        public TenantStatus $status = TenantStatus::Pending,
    ) {}
}
