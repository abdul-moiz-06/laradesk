<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\AuditableEvent;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantCreated implements AuditableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Tenant $tenant,
    ) {}

    public function auditDescription(): string
    {
        return 'Company created';
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
        return ['name' => $this->tenant->name];
    }
}
