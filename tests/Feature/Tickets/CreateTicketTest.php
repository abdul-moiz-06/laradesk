<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Enums\TicketStatus;
use App\Models\Tenant;
use App\Models\User;

it('creates a ticket for the current tenant', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $tenant->makeCurrent();

    $ticket = app(CreateTicket::class)(new CreateTicketDTO(
        title: 'Cannot log in',
        description: 'The login page returns an error.',
        customerId: $customer->id,
    ));

    expect($ticket->tenant_id)->toBe($tenant->id)
        ->and($ticket->status)->toBe(TicketStatus::Open)
        ->and($ticket->customer_id)->toBe($customer->id);

    $this->assertDatabaseHas('tickets', [
        'title' => 'Cannot log in',
        'tenant_id' => $tenant->id,
        'status' => 'open',
    ]);
});
