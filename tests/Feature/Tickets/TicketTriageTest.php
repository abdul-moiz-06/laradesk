<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Enums\TicketCategory;
use App\Models\Tenant;
use App\Models\User;

it('triages a ticket through the AI pipeline when it is created', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $tenant->makeCurrent();
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);

    $ticket = app(CreateTicket::class)(new CreateTicketDTO('Bug', 'Something broke', $customer->id));

    // FakeAIService (bound for Feature tests) returns Technical + a draft; queue runs sync.
    expect($ticket->fresh()->category)->toBe(TicketCategory::Technical)
        ->and($ticket->fresh()->ai_draft_reply)->not->toBeNull();
});
