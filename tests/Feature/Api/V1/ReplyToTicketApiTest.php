<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lets a customer reply to their own ticket', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Issue', 'body', $customer->id));

    Sanctum::actingAs($customer);

    $this->postJson("/api/v1/tickets/{$ticket->ulid}/replies", ['body' => 'Please help'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Please help');

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $ticket->id,
        'user_id' => $customer->id,
        'body' => 'Please help',
        'is_ai_draft' => false,
    ]);
});

it('forbids replying to another customer ticket', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $other = User::factory()->create(['tenant_id' => $tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Not yours', 'body', $other->id));

    Sanctum::actingAs($customer);

    $this->postJson("/api/v1/tickets/{$ticket->ulid}/replies", ['body' => 'hi'])
        ->assertForbidden();
});

it('validates the reply body', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Issue', 'body', $customer->id));

    Sanctum::actingAs($customer);

    $this->postJson("/api/v1/tickets/{$ticket->ulid}/replies", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['body']);
});
