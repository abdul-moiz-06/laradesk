<?php

declare(strict_types=1);

use App\Actions\Agents\CreateAgent;
use App\DTOs\CreateAgentDTO;
use App\Enums\Role;
use App\Models\Tenant;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

it('creates an agent in the current tenant and sends a password invite', function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    Notification::fake();

    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();

    $agent = app(CreateAgent::class)(new CreateAgentDTO('Alice Agent', 'Alice@Acme.test'));

    expect($agent->tenant_id)->toBe($tenant->id)
        ->and($agent->email)->toBe('alice@acme.test')
        ->and($agent->hasRole(Role::Agent->value))->toBeTrue();

    Notification::assertSentTo($agent, ResetPassword::class);
});
