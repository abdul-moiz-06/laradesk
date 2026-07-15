<?php

declare(strict_types=1);

namespace App\Filament\Super\Widgets;

use App\Contracts\PlatformMetricsContract;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

/**
 * New companies signed up per month, over the last half year, as a signal of
 * platform growth.
 */
class CompaniesGrowthChart extends ChartWidget
{
    /**
     * Number of trailing months to plot.
     */
    private const int MONTHS = 6;

    protected static ?int $sort = 2;

    protected ?string $heading = 'New companies per month';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Tenant::class) ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $series = app(PlatformMetricsContract::class)->companiesPerMonth(self::MONTHS);

        $labels = [];

        foreach (array_keys($series) as $month) {
            $labels[] = CarbonImmutable::parse("{$month}-01")->format('M Y');
        }

        return [
            'datasets' => [
                [
                    'label' => 'New companies',
                    'data' => array_values($series),
                ],
            ],
            'labels' => $labels,
        ];
    }
}
