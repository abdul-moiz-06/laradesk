<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PlatformMetricsContract;
use App\DTOs\CompanyMetricsDTO;
use App\DTOs\PlatformMetricsDTO;
use App\Enums\TenantStatus;
use App\Enums\TicketStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;

/**
 * Reads platform-wide, cross-company metrics for the SuperAdmin dashboard.
 *
 * Every read here spans all tenants, so it deliberately runs on the gated,
 * privileged `pgsql_admin` connection (the only path allowed to bypass the
 * row-level tenant isolation) and never on the tenant-governed default one,
 * which would fail closed and return nothing. Only aggregate counts are read,
 * never a customer's ticket content, so the platform operator sees numbers,
 * not another company's data.
 */
final class PlatformMetricsService implements PlatformMetricsContract
{
    /**
     * The gated connection reserved for cross-company platform reads.
     */
    private const string CONNECTION = 'pgsql_admin';

    /**
     * How long (seconds) each aggregate is cached. Short, because it feeds a
     * live dashboard, but long enough to spare the database repeat scans.
     */
    private const int CACHE_TTL = 60;

    public function __construct(
        private readonly ConnectionResolverInterface $db,
        private readonly Cache $cache,
    ) {}

    public function overview(): PlatformMetricsDTO
    {
        /** @var PlatformMetricsDTO $overview */
        $overview = $this->cache->remember(
            'platform:metrics:overview',
            self::CACHE_TTL,
            fn (): PlatformMetricsDTO => $this->computeOverview(),
        );

        return $overview;
    }

    public function perCompany(): array
    {
        /** @var list<CompanyMetricsDTO> $companies */
        $companies = $this->cache->remember(
            'platform:metrics:companies',
            self::CACHE_TTL,
            fn (): array => $this->computePerCompany(),
        );

        return $companies;
    }

    public function companiesPerMonth(int $months): array
    {
        /** @var array<string, int> $series */
        $series = $this->cache->remember(
            "platform:metrics:companies-per-month:{$months}",
            self::CACHE_TTL,
            fn (): array => $this->computeCompaniesPerMonth($months),
        );

        return $series;
    }

    private function computeOverview(): PlatformMetricsDTO
    {
        $connection = $this->connection();

        return new PlatformMetricsDTO(
            totalCompanies: $connection->table('tenants')->count(),
            activeCompanies: $connection->table('tenants')->where('status', TenantStatus::Active->value)->count(),
            suspendedCompanies: $connection->table('tenants')->where('status', TenantStatus::Suspended->value)->count(),
            totalTickets: $connection->table('tickets')->count(),
            openTickets: $connection->table('tickets')->where('status', TicketStatus::Open->value)->count(),
            totalUsers: $connection->table('users')->whereNotNull('tenant_id')->count(),
        );
    }

    /**
     * @return list<CompanyMetricsDTO>
     */
    private function computePerCompany(): array
    {
        $connection = $this->connection();

        $ticketTotals = $this->countByTenant($connection->table('tickets'));
        $openTotals = $this->countByTenant($connection->table('tickets')->where('status', TicketStatus::Open->value));
        $userTotals = $this->countByTenant($connection->table('users')->whereNotNull('tenant_id'));

        $companies = [];

        foreach (
            $connection->table('tenants')
                ->select(['id', 'name', 'plan', 'status', 'created_at'])
                ->orderBy('name')
                ->get() as $tenant
        ) {
            $id = $this->toInt($tenant->id);

            $companies[] = new CompanyMetricsDTO(
                id: $id,
                name: $this->toString($tenant->name),
                plan: $tenant->plan === null ? null : $this->toString($tenant->plan),
                status: TenantStatus::from($this->toString($tenant->status)),
                usersCount: $userTotals[$id] ?? 0,
                ticketsCount: $ticketTotals[$id] ?? 0,
                openTicketsCount: $openTotals[$id] ?? 0,
                createdAt: CarbonImmutable::parse($this->toString($tenant->created_at)),
            );
        }

        return $companies;
    }

    /**
     * @return array<string, int>
     */
    private function computeCompaniesPerMonth(int $months): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        $counts = [];

        foreach (
            $this->connection()->table('tenants')
                ->where('created_at', '>=', $start)
                ->selectRaw("to_char(created_at, 'YYYY-MM') as ym, count(*) as aggregate")
                ->groupBy('ym')
                ->get() as $row
        ) {
            $counts[$this->toString($row->ym)] = $this->toInt($row->aggregate);
        }

        $series = [];

        for ($offset = 0; $offset < $months; $offset++) {
            $key = $start->addMonths($offset)->format('Y-m');
            $series[$key] = $counts[$key] ?? 0;
        }

        return $series;
    }

    /**
     * Group a ticket/user query by tenant and return tenant_id => count.
     *
     * @return array<int, int>
     */
    private function countByTenant(Builder $query): array
    {
        $counts = [];

        foreach (
            $query->groupBy('tenant_id')
                ->selectRaw('tenant_id, count(*) as aggregate')
                ->get() as $row
        ) {
            $counts[$this->toInt($row->tenant_id)] = $this->toInt($row->aggregate);
        }

        return $counts;
    }

    private function connection(): ConnectionInterface
    {
        return $this->db->connection(self::CONNECTION);
    }

    /**
     * Narrow a raw database value (typed as mixed by the query builder) to an
     * integer count.
     */
    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Narrow a raw database value (typed as mixed by the query builder) to a
     * string.
     */
    private function toString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
