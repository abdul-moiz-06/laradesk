<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    $this->tenant = Tenant::create(['name' => 'Acme', 'domain' => 'acme.test', 'status' => 'active']);
    $this->tenant->makeCurrent();
});

it('sends a panel user who has not set up two-factor to the set-up page', function (): void {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole(Role::TenantAdmin->value);

    // fresh() loads the (null) MFA columns; this user has not enrolled in 2FA.
    $this->actingAs($admin->fresh())->get('/admin')->assertRedirect();
});

it('lets a panel user who has set up two-factor straight in', function (): void {
    $admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $admin->assignRole(Role::TenantAdmin->value);

    $this->actingAs(withMfa($admin))->get('/admin')->assertOk();
});

it('stores the two-factor secret and recovery codes encrypted at rest', function (): void {
    $user = withMfa(User::factory()->create(['tenant_id' => $this->tenant->id]));
    $user->saveAppAuthenticationRecoveryCodes(['code-one', 'code-two']);

    // The model exposes the decrypted values, but the raw column is ciphertext.
    expect($user->getAppAuthenticationRecoveryCodes())->toBe(['code-one', 'code-two']);

    $raw = DB::table('users')->where('id', $user->id)->value('app_authentication_recovery_codes');
    expect($raw)->not->toContain('code-one');
});
