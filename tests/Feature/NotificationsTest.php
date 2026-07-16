<?php

declare(strict_types=1);

use App\Actions\Tickets\AssignTicket;
use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\ReplyToTicketDTO;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketReplyNotification;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $this->tenant->makeCurrent();

    $this->agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->agent->assignRole(Role::Agent->value);

    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->customer->assignRole(Role::Customer->value);
});

function ticketFor(User $customer, ?int $agentId = null): Ticket
{
    return Ticket::create([
        'title' => 'Order not delivered',
        'description' => 'It has been a week.',
        'status' => 'open',
        'customer_id' => $customer->id,
        'assigned_agent_id' => $agentId,
    ]);
}

it('notifies the agent when a ticket is assigned to them', function (): void {
    Notification::fake();

    app(AssignTicket::class)(ticketFor($this->customer), $this->agent);

    Notification::assertSentTo($this->agent, TicketAssignedNotification::class);
});

it('notifies the assigned agent when a customer replies', function (): void {
    Notification::fake();
    $ticket = ticketFor($this->customer, $this->agent->id);

    app(ReplyToTicket::class)(new ReplyToTicketDTO($ticket->id, $this->customer->id, 'Any update?'));

    Notification::assertSentTo($this->agent, TicketReplyNotification::class);
});

it('does not notify when the assigned agent replies to their own ticket', function (): void {
    Notification::fake();
    $ticket = ticketFor($this->customer, $this->agent->id);

    app(ReplyToTicket::class)(new ReplyToTicketDTO($ticket->id, $this->agent->id, 'On it now.'));

    Notification::assertNothingSent();
});

it('does not notify when a reply lands on an unassigned ticket', function (): void {
    Notification::fake();
    $ticket = ticketFor($this->customer);

    app(ReplyToTicket::class)(new ReplyToTicketDTO($ticket->id, $this->customer->id, 'Hello?'));

    Notification::assertNothingSent();
});
