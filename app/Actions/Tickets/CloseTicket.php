<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;

final class CloseTicket
{
    public function __invoke(Ticket $ticket): Ticket
    {
        $ticket->status = TicketStatus::Closed;
        $ticket->save();

        return $ticket;
    }
}
