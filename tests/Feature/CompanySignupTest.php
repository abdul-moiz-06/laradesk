<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
});

it('renders the marketing landing', function (): void {
    $this->get('/')->assertOk()->assertSee('AI-powered support');
});

it('renders the signup form', function (): void {
    $this->get('/signup')->assertOk()->assertSee('Create your company workspace');
});

it('registers a company and invites the first admin', function (): void {
    Notification::fake();

    $this->post('/signup', [
        'company_name' => 'Acme Inc',
        'name' => 'Ann Admin',
        'email' => 'ann@acme.test',
    ])->assertRedirect(route('signup.thanks'));

    $tenant = Tenant::query()->where('name', 'Acme Inc')->first();

    expect($tenant)->not->toBeNull()
        ->and($tenant->domain)->toBe('acme-inc')
        ->and($tenant->status)->toBe(TenantStatus::Active);

    $admin = User::query()->where('email', 'ann@acme.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->tenant_id)->toBe($tenant->id)
        ->and($admin->hasRole(Role::TenantAdmin->value))->toBeTrue();

    Notification::assertSentTo($admin, ResetPassword::class);
});

it('rejects an email already in use', function (): void {
    User::factory()->create(['email' => 'taken@acme.test', 'tenant_id' => null]);

    $this->post('/signup', [
        'company_name' => 'Nope Ltd',
        'name' => 'Someone',
        'email' => 'taken@acme.test',
    ])->assertSessionHasErrors('email');

    expect(Tenant::query()->where('name', 'Nope Ltd')->exists())->toBeFalse();
});

it('gives each company a unique domain', function (): void {
    Notification::fake();

    $this->post('/signup', ['company_name' => 'Acme Inc', 'name' => 'A', 'email' => 'a1@acme.test']);
    $this->post('/signup', ['company_name' => 'Acme Inc', 'name' => 'B', 'email' => 'a2@acme.test']);

    $domains = Tenant::query()->where('name', 'Acme Inc')->pluck('domain')->all();

    expect($domains)->toContain('acme-inc')->toContain('acme-inc-2');
});
