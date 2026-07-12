<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role as RoleModel;

it('belongs to a tenant', function (): void {
    $tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    expect($user->tenant)->not->toBeNull()
        ->and($user->tenant->id)->toBe($tenant->id);
});

it('can be assigned a role', function (): void {
    RoleModel::findOrCreate(Role::Agent->value);
    $user = User::factory()->create();

    $user->assignRole(Role::Agent->value);

    expect($user->hasRole(Role::Agent->value))->toBeTrue();
});
