<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\DTOs\ReplyToTicketDTO;
use App\Models\TicketReply;

final class ReplyToTicket
{
    public function __invoke(ReplyToTicketDTO $data): TicketReply
    {
        return TicketReply::create([
            'ticket_id' => $data->ticketId,
            'user_id' => $data->userId,
            'body' => $data->body,
            'is_ai_draft' => false,
        ]);
    }
}
