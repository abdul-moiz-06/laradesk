<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\CreateTicketDTO;
use App\DTOs\ReplyToTicketDTO;
use App\Models\Tenant;
use App\Models\User;

it('creates a non-draft reply stamped with the current tenant and author', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Issue', 'body', $customer->id));

    $reply = app(ReplyToTicket::class)(new ReplyToTicketDTO($ticket->id, $customer->id, 'Hello'));

    expect($reply->ticket_id)->toBe($ticket->id)
        ->and($reply->user_id)->toBe($customer->id)
        ->and($reply->body)->toBe('Hello')
        ->and($reply->is_ai_draft)->toBeFalse()
        ->and($reply->tenant_id)->toBe($tenant->id);
});
