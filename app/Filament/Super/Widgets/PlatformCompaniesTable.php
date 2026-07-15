<?php

declare(strict_types=1);

namespace App\Filament\Super\Widgets;

use App\Contracts\PlatformMetricsContract;
use App\DTOs\CompanyMetricsDTO;
use App\Models\Tenant;
use Filament\Widgets\Widget;

/**
 * A per-company breakdown of ticket and user counts. This is the flagship
 * cross-company view; its data is read only through the gated platform service.
 */
class PlatformCompaniesTable extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.super.widgets.platform-companies-table';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Tenant::class) ?? false;
    }

    /**
     * @return list<CompanyMetricsDTO>
     */
    public function getCompanies(): array
    {
        return app(PlatformMetricsContract::class)->perCompany();
    }
}
