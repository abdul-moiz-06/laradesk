<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Contracts\AIServiceContract;
use App\Models\Ticket;

final readonly class ProcessTicketWithAI
{
    public function __construct(
        private AIServiceContract $ai,
    ) {}

    public function __invoke(Ticket $ticket): void
    {
        $result = $this->ai->analyze($ticket->title, $ticket->description);

        $ticket->update([
            'category' => $result->category,
            'sentiment' => $result->sentiment,
            'priority' => $result->priority,
            'ai_draft_reply' => $result->draftReply,
        ]);
    }
}
