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

it('matches the login e-mail case-insensitively', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    // Stored with mixed case + spaces — the model normalises it to 'jane@acme.test'.
    $user = User::factory()->create(['tenant_id' => $tenant->id, 'email' => '  Jane@Acme.test  ']);
    expect($user->email)->toBe('jane@acme.test');

    $response = $this->postJson('/api/v1/tokens', [
        'email' => 'JANE@ACME.TEST',
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
