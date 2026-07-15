<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantAdminInvited implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public User $admin,
    ) {}

    public function auditDescription(): string
    {
        return 'Company admin invited';
    }

    public function auditSubject(): Model
    {
        return $this->tenant;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditProperties(): array
    {
        return ['email' => $this->admin->email];
    }
}
