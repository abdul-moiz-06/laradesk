<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the current tenant: every query is filtered by `tenant_id`,
 * and new records are stamped with the current tenant automatically.
 *
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') === null && Tenant::checkCurrent()) {
                $model->setAttribute('tenant_id', Tenant::current()?->getKey());
            }
        });

        static::addGlobalScope('tenant', function (Builder $query): void {
            if (Tenant::checkCurrent()) {
                $query->where(
                    $query->getModel()->qualifyColumn('tenant_id'),
                    Tenant::current()?->getKey(),
                );
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
