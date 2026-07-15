<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\TicketReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketReplied implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public TicketReply $reply,
    ) {}

    public function auditDescription(): string
    {
        return 'Ticket reply added';
    }

    public function auditSubject(): Model
    {
        return $this->reply;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditProperties(): array
    {
        return ['ticket_id' => $this->reply->ticket_id];
    }
}
