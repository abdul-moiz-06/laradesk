<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * A domain event that should be recorded in the audit trail. One listener
 * (RecordActivity) handles every implementor, so adding an audited action is
 * just a new event, with no change to the recording logic.
 */
interface AuditableEvent
{
    public function auditDescription(): string;

    public function auditSubject(): Model;

    /**
     * @return array<string, mixed>
     */
    public function auditProperties(): array;
}
