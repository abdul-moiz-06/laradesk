<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('never lets a customer read another tenant ticket over the API', function (): void {
    $tenantA = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);

    $tenantB->makeCurrent();
    $customerB = User::factory()->create(['tenant_id' => $tenantB->id]);
    $ticketB = app(CreateTicket::class)(new CreateTicketDTO('B secret', 'body', $customerB->id));

    $tenantA->makeCurrent();
    $customerA = User::factory()->create(['tenant_id' => $tenantA->id]);

    Sanctum::actingAs($customerA);

    // Tenant B's ticket is invisible under Tenant A's scope — resolves to 404, not 403.
    $this->getJson("/api/v1/tickets/{$ticketB->ulid}")->assertNotFound();

    // And it never appears in Tenant A's own list.
    $this->getJson('/api/v1/tickets')->assertOk()->assertJsonCount(0, 'data');
});
