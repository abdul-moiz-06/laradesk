<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketClosed implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function auditDescription(): string
    {
        return 'Ticket closed';
    }

    public function auditSubject(): Model
    {
        return $this->ticket;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditProperties(): array
    {
        return [];
    }
}
