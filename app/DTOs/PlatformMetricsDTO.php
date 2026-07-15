<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Platform-wide totals across every company, for the SuperAdmin dashboard.
 * Counts only, never any company's ticket content.
 */
final readonly class PlatformMetricsDTO
{
    public function __construct(
        public int $totalCompanies,
        public int $activeCompanies,
        public int $suspendedCompanies,
        public int $totalTickets,
        public int $openTickets,
        public int $totalUsers,
    ) {}
}
