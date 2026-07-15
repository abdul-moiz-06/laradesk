<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A SuperAdmin stopped acting as a company user and returned to the platform.
 * Fired after the SuperAdmin identity is restored, so the audit causer is the
 * SuperAdmin, and recorded in the affected company's own trail.
 */
final class ImpersonationEnded implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public User $target,
    ) {}

    public function auditDescription(): string
    {
        return 'Impersonation ended';
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
        ];
    }
}
