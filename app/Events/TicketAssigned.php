<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketAssigned implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public User $agent,
    ) {}

    public function auditDescription(): string
    {
        return 'Ticket assigned';
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
        return ['agent_id' => $this->agent->id, 'agent_name' => $this->agent->name];
    }
}
