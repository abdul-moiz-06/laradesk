<?php

declare(strict_types=1);

use App\Contracts\PlatformMetricsContract;
use App\Enums\TenantStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * The service reads across every tenant, which is only possible on the gated
 * `pgsql_admin` connection. That connection cannot see another connection's
 * open transaction, so the fixtures are written on it directly, inside its own
 * transaction, and rolled back afterwards. RefreshDatabase keeps the schema
 * migrated and isolates the default connection.
 */
uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = DB::connection('pgsql_admin');
    $this->admin->beginTransaction();
});

afterEach(function (): void {
    $this->admin->rollBack();
});

function seedCompany(string $name, TenantStatus $status): int
{
    return (int) DB::connection('pgsql_admin')->table('tenants')->insertGetId([
        'name' => $name,
        'domain' => strtolower($name).'.test',
        'status' => $status->value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedCustomer(int $tenantId, string $email): int
{
    return (int) DB::connection('pgsql_admin')->table('users')->insertGetId([
        'name' => 'Customer',
        'email' => $email,
        'password' => 'x',
        'tenant_id' => $tenantId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedTicket(int $tenantId, int $customerId, TicketStatus $status): void
{
    $ticket = new Ticket;
    $ticket->setConnection('pgsql_admin');
    $ticket->tenant_id = $tenantId;
    $ticket->customer_id = $customerId;
    $ticket->title = 'Demo';
    $ticket->description = 'Demo description';
    $ticket->status = $status;
    $ticket->save();
}

it('totals companies, tickets and users across every tenant', function (): void {
    $acme = seedCompany('Acme', TenantStatus::Active);
    $globex = seedCompany('Globex', TenantStatus::Suspended);

    $acmeCustomer = seedCustomer($acme, 'a@acme.test');
    $globexCustomer = seedCustomer($globex, 'b@globex.test');

    seedTicket($acme, $acmeCustomer, TicketStatus::Open);
    seedTicket($acme, $acmeCustomer, TicketStatus::Closed);
    seedTicket($globex, $globexCustomer, TicketStatus::Open);

    $overview = app(PlatformMetricsContract::class)->overview();

    expect($overview->totalCompanies)->toBe(2)
        ->and($overview->activeCompanies)->toBe(1)
        ->and($overview->suspendedCompanies)->toBe(1)
        ->and($overview->totalTickets)->toBe(3)
        ->and($overview->openTickets)->toBe(2)
        ->and($overview->totalUsers)->toBe(2);
});

it('breaks ticket and user counts down per company, ordered by name', function (): void {
    $acme = seedCompany('Acme', TenantStatus::Active);
    $globex = seedCompany('Globex', TenantStatus::Suspended);

    $acmeCustomer = seedCustomer($acme, 'a@acme.test');
    seedCustomer($globex, 'b@globex.test');

    seedTicket($acme, $acmeCustomer, TicketStatus::Open);
    seedTicket($acme, $acmeCustomer, TicketStatus::Closed);

    $companies = app(PlatformMetricsContract::class)->perCompany();

    expect($companies)->toHaveCount(2)
        ->and($companies[0]->name)->toBe('Acme')
        ->and($companies[0]->status)->toBe(TenantStatus::Active)
        ->and($companies[0]->ticketsCount)->toBe(2)
        ->and($companies[0]->openTicketsCount)->toBe(1)
        ->and($companies[0]->usersCount)->toBe(1)
        ->and($companies[1]->name)->toBe('Globex')
        ->and($companies[1]->status)->toBe(TenantStatus::Suspended)
        ->and($companies[1]->ticketsCount)->toBe(0)
        ->and($companies[1]->usersCount)->toBe(1);
});
