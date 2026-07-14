<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Gives a model an app-generated public ULID used for route-model binding and
 * API exposure, so the auto-increment primary key is never leaked in URLs.
 *
 * @mixin Model
 */
trait HasPublicUlid
{
    public static function bootHasPublicUlid(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('ulid'))) {
                $model->setAttribute('ulid', (string) Str::ulid());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
