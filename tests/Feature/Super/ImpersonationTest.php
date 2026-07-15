<?php

declare(strict_types=1);

use App\Actions\Impersonation\StartImpersonation;
use App\Actions\Impersonation\StopImpersonation;
use App\Enums\Role;
use App\Events\AgentInvited;
use App\Http\Middleware\EnforceImpersonationTimebox;
use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);

    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);

    $this->superAdmin = withMfa(User::factory()->create(['tenant_id' => null]));
    $this->superAdmin->assignRole(Role::SuperAdmin->value);

    $this->target = withMfa(User::factory()->create(['tenant_id' => $this->tenant->id]));
    $this->target->assignRole(Role::TenantAdmin->value);
});

it('permits a super admin to impersonate onboarded company staff', function (): void {
    expect($this->superAdmin->can('impersonate', $this->target))->toBeTrue();
});

it('forbids impersonating a user who has not set up two-factor', function (): void {
    $fresh = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $fresh->assignRole(Role::Agent->value);

    expect($this->superAdmin->can('impersonate', $fresh))->toBeFalse();
});

it('forbids impersonating staff of a suspended company', function (): void {
    $this->tenant->update(['status' => 'suspended']);

    expect($this->superAdmin->can('impersonate', $this->target))->toBeFalse();
});

it('forbids a non super admin from impersonating', function (): void {
    $admin = withMfa(User::factory()->create(['tenant_id' => $this->tenant->id]));
    $admin->assignRole(Role::TenantAdmin->value);

    expect($admin->can('impersonate', $this->target))->toBeFalse();
});

it('starts impersonation, swaps identity, and records it in the company trail', function (): void {
    $this->actingAs($this->superAdmin);

    app(StartImpersonation::class)($this->target, 'Reproducing a billing issue');

    expect(auth()->id())->toBe($this->target->id)
        ->and(session('impersonator_id'))->toBe($this->superAdmin->id)
        ->and(session('impersonator_email'))->toBe($this->superAdmin->email);

    $this->tenant->makeCurrent();
    $activity = Activity::where('event', 'ImpersonationStarted')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($this->superAdmin->id)
        ->and($activity->tenant_id)->toBe($this->tenant->id)
        ->and($activity->properties['reason'])->toBe('Reproducing a billing issue');
});

it('stops impersonation, restores the super admin, and audits the end', function (): void {
    $this->actingAs($this->superAdmin);
    app(StartImpersonation::class)($this->target, 'reason');
    expect(auth()->id())->toBe($this->target->id);

    app(StopImpersonation::class)();

    expect(auth()->id())->toBe($this->superAdmin->id)
        ->and(session('impersonator_id'))->toBeNull();

    $this->tenant->makeCurrent();
    expect(Activity::where('event', 'ImpersonationEnded')->exists())->toBeTrue();
});

it('stamps in-impersonation actions with the operator behind them', function (): void {
    $this->actingAs($this->superAdmin);
    app(StartImpersonation::class)($this->target, 'reason');

    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
    event(new AgentInvited($agent));

    $this->tenant->makeCurrent();
    $activity = Activity::where('event', 'AgentInvited')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties['impersonated_by'])->toBe($this->superAdmin->email);
});

it('ends an impersonation session once its time box passes', function (): void {
    $this->actingAs($this->superAdmin);
    app(StartImpersonation::class)($this->target, 'reason');
    expect(auth()->id())->toBe($this->target->id);

    session(['impersonation_expires_at' => CarbonImmutable::now()->subMinute()->toIso8601String()]);

    $response = app(EnforceImpersonationTimebox::class)
        ->handle(request(), fn (): Response => new Response('reached'));

    expect($response->getStatusCode())->toBe(302)
        ->and(auth()->id())->toBe($this->superAdmin->id)
        ->and(session('impersonator_id'))->toBeNull();
});

it('returns the operator to the platform when the banner stop is posted', function (): void {
    $this->actingAs($this->target)
        ->withSession([
            'impersonator_id' => $this->superAdmin->id,
            'impersonator_email' => $this->superAdmin->email,
        ])
        ->post('/impersonation/stop')
        ->assertRedirect('/super')
        ->assertSessionMissing('impersonator_id');
});
