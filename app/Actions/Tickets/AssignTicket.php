<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

final class AssignTicket
{
    public function __invoke(Ticket $ticket, User $agent): Ticket
    {
        $ticket->assigned_agent_id = $agent->id;

        if ($ticket->status === TicketStatus::Open) {
            $ticket->status = TicketStatus::Pending;
        }

        $ticket->save();

        return $ticket;
    }
}
