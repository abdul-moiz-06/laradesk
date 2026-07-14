<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;

it('issues a token for valid credentials', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    User::factory()->create(['tenant_id' => $tenant->id, 'email' => 'jane@acme.test']);

    $response = $this->postJson('/api/v1/tokens', [
        'email' => 'jane@acme.test',
        'password' => 'password',
        'device_name' => 'iphone',
    ]);

    $response->assertCreated()->assertJsonStructure(['token']);
});

it('rejects invalid credentials with a 401', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    User::factory()->create(['tenant_id' => $tenant->id, 'email' => 'jane@acme.test']);

    $response = $this->postJson('/api/v1/tokens', [
        'email' => 'jane@acme.test',
        'password' => 'wrong-password',
        'device_name' => 'iphone',
    ]);

    $response->assertUnauthorized()->assertJsonPath('code', 'invalid_credentials');
});

it('validates the token request', function (): void {
    $response = $this->postJson('/api/v1/tokens', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password', 'device_name']);
});
