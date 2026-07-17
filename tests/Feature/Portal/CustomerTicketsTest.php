<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Livewire\Portal\ViewTicket;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketReplyNotification;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
});

function portalTicket(int $customerId, ?int $agentId = null): Ticket
{
    return Ticket::create([
        'title' => 'My issue',
        'description' => 'Something is wrong.',
        'status' => 'open',
        'customer_id' => $customerId,
        'assigned_agent_id' => $agentId,
    ]);
}

it('shows a customer only their own tickets', function (): void {
    $customer = customerFor($this->tenant);
    $other = customerFor($this->tenant);
    $this->tenant->makeCurrent();
    Ticket::create(['title' => 'Mine to see', 'description' => 'x', 'status' => 'open', 'customer_id' => $customer->id]);
    Ticket::create(['title' => 'Not for me', 'description' => 'x', 'status' => 'open', 'customer_id' => $other->id]);

    $this->actingAs($customer, 'customer')
        ->get('/portal')
        ->assertOk()
        ->assertSee('Mine to see')
        ->assertDontSee('Not for me');
});

it('shows the customer their own ticket', function (): void {
    $customer = customerFor($this->tenant);
    $this->tenant->makeCurrent();
    $ticket = portalTicket($customer->id);

    $this->actingAs($customer, 'customer')
        ->get('/portal/tickets/'.$ticket->ulid)
        ->assertOk()
        ->assertSee('My issue');
});

it('lets a customer reply and notifies the assigned agent', function (): void {
    Notification::fake();
    $agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $agent->assignRole(Role::Agent->value);
    $customer = customerFor($this->tenant);
    $this->tenant->makeCurrent();
    $ticket = portalTicket($customer->id, $agent->id);

    $this->actingAs($customer, 'customer');

    Livewire::test(ViewTicket::class, ['ulid' => $ticket->ulid])
        ->set('body', 'Any update on this?')
        ->call('reply')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $ticket->id,
        'user_id' => $customer->id,
        'body' => 'Any update on this?',
    ]);

    Notification::assertSentTo($agent, TicketReplyNotification::class);
});

it('does not let a customer view another customer ticket in the same company', function (): void {
    $customer = customerFor($this->tenant);
    $other = customerFor($this->tenant);
    $this->tenant->makeCurrent();
    $ticket = portalTicket($other->id);

    $this->actingAs($customer, 'customer')
        ->get('/portal/tickets/'.$ticket->ulid)
        ->assertNotFound();
});

it('does not let a customer view another company ticket', function (): void {
    $customer = customerFor($this->tenant);
    $globex = Tenant::create(['name' => 'Globex', 'domain' => 'globex.test', 'status' => 'active']);
    $globexCustomer = customerFor($globex);
    $globex->makeCurrent();
    $ticket = portalTicket($globexCustomer->id);

    $this->actingAs($customer, 'customer')
        ->get('/portal/tickets/'.$ticket->ulid)
        ->assertNotFound();
});
