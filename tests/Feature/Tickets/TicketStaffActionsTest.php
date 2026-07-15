<?php

declare(strict_types=1);

use App\Actions\Tickets\AssignTicket;
use App\Actions\Tickets\CloseTicket;
use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Enums\TicketStatus;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function (): void {
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $this->tenant->makeCurrent();
    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

it('assigns a ticket to an agent and moves an open ticket to pending', function (): void {
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Issue', 'body', $this->customer->id));

    $result = app(AssignTicket::class)($ticket, $agent);

    expect($result->assigned_agent_id)->toBe($agent->id)
        ->and($result->status)->toBe(TicketStatus::Pending);
});

it('closes a ticket', function (): void {
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Issue', 'body', $this->customer->id));

    expect(app(CloseTicket::class)($ticket)->status)->toBe(TicketStatus::Closed);
});
