<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists only the authenticated customer own tickets, paginated', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $other = User::factory()->create(['tenant_id' => $tenant->id]);

    app(CreateTicket::class)(new CreateTicketDTO('Mine', 'body', $customer->id));
    app(CreateTicket::class)(new CreateTicketDTO('Theirs', 'body', $other->id));

    Sanctum::actingAs($customer);

    $this->getJson('/api/v1/tickets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Mine')
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('requires authentication to list tickets', function (): void {
    $this->getJson('/api/v1/tickets')->assertUnauthorized();
});
