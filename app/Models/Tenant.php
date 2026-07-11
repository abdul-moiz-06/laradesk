<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

/**
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property string|null $plan
 * @property TenantStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['name', 'domain', 'plan', 'status'])]
class Tenant extends BaseTenant
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
        ];
    }
}
