<?php

declare(strict_types=1);

use App\Enums\TicketCategory;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates a ticket for the authenticated customer', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/v1/tickets', [
        'title' => 'Cannot log in',
        'description' => 'The login page returns an error.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Cannot log in')
        ->assertJsonPath('data.status', 'open');

    $this->assertDatabaseHas('tickets', [
        'title' => 'Cannot log in',
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
    ]);
});

it('triages the created ticket through the AI pipeline', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    Sanctum::actingAs($customer);

    $this->postJson('/api/v1/tickets', [
        'title' => 'Bug',
        'description' => 'Something broke.',
    ])->assertCreated();

    // FakeAIService (bound for feature tests) returns Technical synchronously.
    $this->assertDatabaseHas('tickets', ['category' => TicketCategory::Technical->value]);
});

it('requires authentication to create a ticket', function (): void {
    $this->postJson('/api/v1/tickets', ['title' => 'x', 'description' => 'y'])
        ->assertUnauthorized();
});

it('validates the ticket payload', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    Sanctum::actingAs($customer);

    $this->postJson('/api/v1/tickets', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'description']);
});
