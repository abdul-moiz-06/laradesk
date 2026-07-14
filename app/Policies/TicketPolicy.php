<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

final class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $ticket->customer_id === $user->id;
    }
}
