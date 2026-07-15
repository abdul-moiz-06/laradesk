<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AgentInvited implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $agent,
    ) {}

    public function auditDescription(): string
    {
        return 'Agent invited';
    }

    public function auditSubject(): Model
    {
        return $this->agent;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditProperties(): array
    {
        return ['email' => $this->agent->email];
    }
}
