<?php

declare(strict_types=1);

use App\Actions\Portal\ConsumeLoginLink;
use App\Actions\Portal\RequestLoginLink;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\CustomerLoginLink;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'sara@acme.test']);
    $this->customer->assignRole(Role::Customer->value);
});

it('emails a customer a sign-in link', function (): void {
    Notification::fake();

    $this->post('/portal/login', ['email' => 'sara@acme.test'])->assertRedirect();

    Notification::assertSentTo($this->customer, CustomerLoginLink::class);
});

it('never reveals whether an email exists', function (): void {
    Notification::fake();

    $this->post('/portal/login', ['email' => 'nobody@nowhere.test'])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

it('does not send a link for a suspended company', function (): void {
    $this->tenant->update(['status' => 'suspended']);
    Notification::fake();

    $this->post('/portal/login', ['email' => 'sara@acme.test']);

    Notification::assertNothingSent();
});

it('does not send a link to staff', function (): void {
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'agent@acme.test']);
    $agent->assignRole(Role::Agent->value);
    Notification::fake();

    $this->post('/portal/login', ['email' => 'agent@acme.test']);

    Notification::assertNothingSent();
});

it('signs the customer in from a valid link', function (): void {
    Cache::put(RequestLoginLink::CACHE_PREFIX.'tok-valid', $this->customer->id, now()->addMinutes(15));

    $this->get('/portal/login/verify/tok-valid')->assertRedirect(route('portal.home'));

    $this->assertAuthenticatedAs($this->customer, 'customer');
});

it('rejects an unknown or expired link', function (): void {
    $this->get('/portal/login/verify/does-not-exist')
        ->assertRedirect(route('portal.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest('customer');
});

it('makes a sign-in link single-use', function (): void {
    Cache::put(RequestLoginLink::CACHE_PREFIX.'tok-once', $this->customer->id, now()->addMinutes(15));
    $consume = app(ConsumeLoginLink::class);

    expect($consume('tok-once'))->not->toBeNull()
        ->and($consume('tok-once'))->toBeNull();
});

it('signs the customer out', function (): void {
    $this->actingAs($this->customer, 'customer')
        ->post('/portal/logout')
        ->assertRedirect(route('portal.login'));

    $this->assertGuest('customer');
});
