<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;

it('relates to its customer and its replies', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);

    $ticket = Ticket::create([
        'title' => 'Help',
        'description' => 'body',
        'customer_id' => $customer->id,
        'status' => 'open',
    ]);
    $reply = TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $customer->id,
        'body' => 'Any update?',
    ]);

    expect($ticket->customer->id)->toBe($customer->id)
        ->and($ticket->replies)->toHaveCount(1)
        ->and($reply->ticket->id)->toBe($ticket->id)
        ->and($reply->tenant_id)->toBe($tenant->id);
});
