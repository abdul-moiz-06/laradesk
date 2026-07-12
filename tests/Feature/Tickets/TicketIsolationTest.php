<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;

it('never leaks tickets across tenants', function (): void {
    $tenantA = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);
    $customerA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $customerB = User::factory()->create(['tenant_id' => $tenantB->id]);

    $tenantA->makeCurrent();
    app(CreateTicket::class)(new CreateTicketDTO('A ticket', 'body', $customerA->id));

    $tenantB->makeCurrent();
    app(CreateTicket::class)(new CreateTicketDTO('B ticket', 'body', $customerB->id));

    // Tenant B is current — it can only see its own ticket.
    expect(Ticket::count())->toBe(1)
        ->and(Ticket::sole()->title)->toBe('B ticket');

    // Switch to Tenant A — only A's ticket is visible.
    $tenantA->makeCurrent();
    expect(Ticket::count())->toBe(1)
        ->and(Ticket::sole()->title)->toBe('A ticket');

    // With no current tenant (e.g. a SuperAdmin context) the global scope is inactive.
    Tenant::forgetCurrent();
    expect(Ticket::count())->toBe(2);
});
