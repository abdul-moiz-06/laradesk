<?php

declare(strict_types=1);

namespace App\Filament\Super\Widgets;

use App\Contracts\PlatformMetricsContract;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Platform-wide headline counts. Authorized through the same SuperAdmin gate as
 * the rest of the platform panel; the data comes from the gated cross-company
 * read service, never a tenant-scoped query.
 */
class PlatformStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Tenant::class) ?? false;
    }

    protected function getStats(): array
    {
        $metrics = app(PlatformMetricsContract::class)->overview();

        return [
            Stat::make('Companies', $metrics->totalCompanies)
                ->description("{$metrics->activeCompanies} active, {$metrics->suspendedCompanies} suspended")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Tickets', $metrics->totalTickets)
                ->description("{$metrics->openTickets} open")
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
            Stat::make('Users', $metrics->totalUsers)
                ->description('Across all companies')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
