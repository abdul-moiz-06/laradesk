<?php

declare(strict_types=1);

use App\Actions\Portal\GenerateSupportLink;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\CustomerLoginLink;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
});

it('builds a signed support link identified by domain', function (): void {
    $url = app(GenerateSupportLink::class)($this->tenant);

    expect($url)->toContain('/portal/submit/acme.test')->toContain('signature=');
});

it('shows the submit form from a valid signed link', function (): void {
    $url = app(GenerateSupportLink::class)($this->tenant);

    $this->get($url)->assertOk()->assertSee('Contact Acme support');
});

it('rejects an unsigned or tampered submit link', function (): void {
    $this->get('/portal/submit/acme.test')->assertForbidden();
});

it('creates a customer and a ticket, and emails a sign-in link', function (): void {
    Notification::fake();
    $url = app(GenerateSupportLink::class)($this->tenant);

    $this->post($url, [
        'name' => 'Sara Khan',
        'email' => 'sara@customer.test',
        'subject' => 'Order not delivered',
        'message' => 'It has been a week.',
    ])->assertRedirect(route('portal.submitted'));

    $customer = User::query()->where('email', 'sara@customer.test')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->tenant_id)->toBe($this->tenant->id)
        ->and($customer->hasRole(Role::Customer->value))->toBeTrue();

    $this->tenant->makeCurrent();
    expect(Ticket::query()->where('customer_id', $customer->id)->where('title', 'Order not delivered')->exists())->toBeTrue();

    Notification::assertSentTo($customer, CustomerLoginLink::class);
});

it('reuses an existing customer of the company', function (): void {
    $customer = User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'existing@customer.test']);
    $customer->assignRole(Role::Customer->value);
    $url = app(GenerateSupportLink::class)($this->tenant);

    $this->post($url, [
        'name' => 'Existing',
        'email' => 'existing@customer.test',
        'subject' => 'Another issue',
        'message' => 'Still broken.',
    ])->assertRedirect(route('portal.submitted'));

    expect(User::query()->where('email', 'existing@customer.test')->count())->toBe(1);
});

it('rejects an email already registered with another company', function (): void {
    $other = Tenant::create(['name' => 'Globex', 'domain' => 'globex.test', 'status' => 'active']);
    $otherCustomer = User::factory()->create(['tenant_id' => $other->id, 'email' => 'taken@customer.test']);
    $otherCustomer->assignRole(Role::Customer->value);
    $url = app(GenerateSupportLink::class)($this->tenant);

    $this->post($url, [
        'name' => 'X',
        'email' => 'taken@customer.test',
        'subject' => 'S',
        'message' => 'M',
    ])->assertSessionHasErrors('email');
});

it('blocks submission to a suspended company', function (): void {
    $url = app(GenerateSupportLink::class)($this->tenant);
    $this->tenant->update(['status' => 'suspended']);

    $this->get($url)->assertForbidden();
});
