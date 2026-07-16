<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Jobs\ProcessTicketWithAI;

final class DispatchAIProcessing
{
    public function handle(TicketCreated $event): void
    {
        ProcessTicketWithAI::dispatch($event->ticket->id)->onQueue('ai');
    }
}
