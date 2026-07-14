<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\CreateTicketDTO;
use App\DTOs\ReplyToTicketDTO;
use App\Models\Tenant;
use App\Models\TicketReply;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('shows a ticket with its published replies only', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('My issue', 'body', $customer->id));

    app(ReplyToTicket::class)(new ReplyToTicketDTO($ticket->id, $customer->id, 'Any update?'));
    TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $customer->id,
        'body' => 'internal AI draft',
        'is_ai_draft' => true,
    ]);

    Sanctum::actingAs($customer);

    $this->getJson("/api/v1/tickets/{$ticket->ulid}")
        ->assertOk()
        ->assertJsonPath('data.id', $ticket->ulid)
        ->assertJsonPath('data.title', 'My issue')
        ->assertJsonCount(1, 'data.replies')
        ->assertJsonPath('data.replies.0.body', 'Any update?');
});

it('forbids viewing another customer ticket in the same tenant', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $other = User::factory()->create(['tenant_id' => $tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Not yours', 'body', $other->id));

    Sanctum::actingAs($customer);

    $this->getJson("/api/v1/tickets/{$ticket->ulid}")->assertForbidden();
});
