<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\DTOs\CreateTicketDTO;
use App\Enums\TicketStatus;
use App\Models\Ticket;

final class CreateTicket
{
    public function __invoke(CreateTicketDTO $data): Ticket
    {
        return Ticket::create([
            'title' => $data->title,
            'description' => $data->description,
            'customer_id' => $data->customerId,
            'status' => TicketStatus::Open,
        ]);
    }
}
