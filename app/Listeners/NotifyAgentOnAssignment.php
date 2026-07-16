<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Notifications\TicketAssignedNotification;

/**
 * Notifies an agent when a ticket is assigned to them.
 */
final class NotifyAgentOnAssignment
{
    public function handle(TicketAssigned $event): void
    {
        $event->agent->notify(new TicketAssignedNotification($event->ticket));
    }
}
