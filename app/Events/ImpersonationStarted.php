<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A SuperAdmin began acting as a company user. Fired while the SuperAdmin is
 * still the authenticated identity, so the audit causer is the SuperAdmin, and
 * recorded in the affected company's own trail.
 */
final class ImpersonationStarted implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $target,
        public string $reason,
        public CarbonImmutable $expiresAt,
    ) {}

    public function auditDescription(): string
    {
        return 'Impersonation started';
    }

    public function auditSubject(): Model
    {
        return $this->target;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditProperties(): array
    {
        return [
            'target' => $this->target->email,
            'reason' => $this->reason,
            'expires_at' => $this->expiresAt->toIso8601String(),
        ];
    }
}
