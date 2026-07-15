<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\TenantStatus;
use Carbon\CarbonImmutable;

/**
 * A single company's row in the platform breakdown: identity plus aggregate
 * ticket and user counts. No ticket content is ever carried here.
 */
final readonly class CompanyMetricsDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $plan,
        public TenantStatus $status,
        public int $usersCount,
        public int $ticketsCount,
        public int $openTicketsCount,
        public CarbonImmutable $createdAt,
    ) {}
}
