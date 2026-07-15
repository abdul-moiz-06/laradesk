<?php

declare(strict_types=1);

use App\Actions\Agents\CreateAgent;
use App\Actions\Tickets\AssignTicket;
use App\Actions\Tickets\CloseTicket;
use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\CreateAgentDTO;
use App\DTOs\ReplyToTicketDTO;
use App\Models\Activity;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $this->tenant->makeCurrent();
    $this->customer = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->agent = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

function auditTicket(): Ticket
{
    // The current tenant is stamped automatically by the BelongsToTenant trait.
    return Ticket::create([
        'title' => 'Issue',
        'description' => 'body',
        'status' => 'open',
        'customer_id' => test()->customer->id,
    ]);
}

it('records a rich audit entry when a ticket is assigned', function (): void {
    $staff = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->actingAs($staff);
    $ticket = auditTicket();

    app(AssignTicket::class)($ticket, $this->agent);

    $activity = Activity::sole();
    expect($activity->event)->toBe('TicketAssigned')
        ->and($activity->description)->toBe('Ticket assigned')
        ->and($activity->causer_id)->toBe($staff->id)
        ->and($activity->subject_type)->toBe(Ticket::class)
        ->and($activity->subject_id)->toBe($ticket->id)
        ->and($activity->tenant_id)->toBe($this->tenant->id)
        ->and($activity->properties['agent_id'])->toBe($this->agent->id);
});

it('records closing a ticket', function (): void {
    app(CloseTicket::class)(auditTicket());

    expect(Activity::where('event', 'TicketClosed')->exists())->toBeTrue();
});

it('records a reply', function (): void {
    $ticket = auditTicket();

    app(ReplyToTicket::class)(new ReplyToTicketDTO($ticket->id, $this->customer->id, 'hello'));

    expect(Activity::where('event', 'TicketReplied')->exists())->toBeTrue();
});

it('records an agent invitation', function (): void {
    Notification::fake();

    app(CreateAgent::class)(new CreateAgentDTO('New Agent', 'new@acme.test'));

    expect(Activity::where('event', 'AgentInvited')->exists())->toBeTrue();
});

it('records a sign-in', function (): void {
    $staff = User::factory()->create(['tenant_id' => $this->tenant->id]);

    event(new Login('web', $staff, false));

    expect(Activity::where('event', 'Login')->where('causer_id', $staff->id)->exists())->toBeTrue();
});

it('never leaks audit entries across tenants', function (): void {
    app(AssignTicket::class)(auditTicket(), $this->agent);

    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);
    $tenantB->makeCurrent();
    $customerB = User::factory()->create(['tenant_id' => $tenantB->id]);
    $agentB = User::factory()->create(['tenant_id' => $tenantB->id]);
    $ticketB = Ticket::create(['title' => 'B', 'description' => 'b', 'status' => 'open', 'customer_id' => $customerB->id]);
    app(AssignTicket::class)($ticketB, $agentB);

    expect(Activity::count())->toBe(1);

    $this->tenant->makeCurrent();
    expect(Activity::count())->toBe(1);
});

it('keeps the audit log append-only at the database', function (): void {
    app(CloseTicket::class)(auditTicket());

    expect(DB::table('activity_log')->update(['description' => 'tampered']))->toBe(0)
        ->and(DB::table('activity_log')->delete())->toBe(0)
        ->and(Activity::count())->toBe(1);
});
