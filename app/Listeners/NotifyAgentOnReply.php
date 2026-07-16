<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TicketReplied;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketReplyNotification;

/**
 * Notifies the assigned agent when a customer replies to their ticket. Skips
 * replies the agent wrote themselves, and tickets with no agent assigned.
 */
final class NotifyAgentOnReply
{
    public function handle(TicketReplied $event): void
    {
        $ticket = Ticket::find($event->reply->ticket_id);

        if ($ticket === null
            || $ticket->assigned_agent_id === null
            || $ticket->assigned_agent_id === $event->reply->user_id) {
            return;
        }

        User::find($ticket->assigned_agent_id)
            ?->notify(new TicketReplyNotification($ticket));
    }
}
