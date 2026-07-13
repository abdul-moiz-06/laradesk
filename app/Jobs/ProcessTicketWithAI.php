<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\AI\ProcessTicketWithAI as ProcessTicketWithAIAction;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessTicketWithAI implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $ticketId,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(ProcessTicketWithAIAction $action): void
    {
        $ticket = Ticket::find($this->ticketId);

        if ($ticket !== null) {
            $action($ticket);
        }
    }
}
