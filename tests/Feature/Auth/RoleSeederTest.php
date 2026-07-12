<?php

declare(strict_types=1);

use App\Enums\Role;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role as RoleModel;

it('seeds the four application roles', function (): void {
    $this->seed(RoleSeeder::class);

    expect(RoleModel::count())->toBe(4)
        ->and(RoleModel::where('name', Role::SuperAdmin->value)->exists())->toBeTrue()
        ->and(RoleModel::where('name', Role::Customer->value)->exists())->toBeTrue();
});
