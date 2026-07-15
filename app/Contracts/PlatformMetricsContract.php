<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\CompanyMetricsDTO;
use App\DTOs\PlatformMetricsDTO;

interface PlatformMetricsContract
{
    /**
     * Platform-wide totals spanning every company.
     */
    public function overview(): PlatformMetricsDTO;

    /**
     * A per-company breakdown of ticket and user counts, ordered by name.
     *
     * @return list<CompanyMetricsDTO>
     */
    public function perCompany(): array;

    /**
     * New companies per calendar month for the last $months months, keyed by
     * "YYYY-MM" and zero-filled for months with no sign-ups.
     *
     * @return array<string, int>
     */
    public function companiesPerMonth(int $months): array;
}
