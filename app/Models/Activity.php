<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Tenant-scoped audit entry. The BelongsToTenant trait stamps the current
 * company on write and fences reads to it; PostgreSQL Row-Level Security backs
 * this at the database and keeps the log append-only.
 *
 * @property int|null $tenant_id
 * @property-read Tenant|null $tenant
 */
class Activity extends SpatieActivity
{
    use BelongsToTenant;
}
