<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Serves two audiences on the same tenant-scoped data: customers (API) act only
 * on their own tickets; staff (panel) act on the whole tenant queue via granular
 * permissions. Checks are additive so the customer/API behaviour is preserved.
 */
final class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        // Row visibility is enforced by the query (tenant scope + ownership),
        // not the gate: customers see their own, staff see the tenant queue.
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->customer_id === $user->id || $user->can('ticket.view');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $ticket->customer_id === $user->id || $user->can('ticket.reply');
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->can('ticket.assign');
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $user->can('ticket.close');
    }
}
