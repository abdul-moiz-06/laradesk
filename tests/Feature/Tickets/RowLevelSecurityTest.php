<?php

declare(strict_types=1);

use App\Actions\Tickets\CreateTicket;
use App\DTOs\CreateTicketDTO;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('fences raw queries to the current tenant even without the Eloquent scope', function (): void {
    $tenantA = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);
    $customerA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $customerB = User::factory()->create(['tenant_id' => $tenantB->id]);

    $tenantA->makeCurrent();
    app(CreateTicket::class)(new CreateTicketDTO('A secret', 'body', $customerA->id));

    $tenantB->makeCurrent();
    app(CreateTicket::class)(new CreateTicketDTO('B secret', 'body', $customerB->id));

    // Query builder — no Eloquent global scope in play; only RLS can fence this.
    expect(DB::table('tickets')->count())->toBe(1)
        ->and(DB::table('tickets')->value('title'))->toBe('B secret');

    $tenantA->makeCurrent();
    expect(DB::table('tickets')->pluck('title')->all())->toBe(['A secret']);
});

it('fails closed when no tenant is current', function (): void {
    $tenant = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $customer = User::factory()->create(['tenant_id' => $tenant->id]);
    $tenant->makeCurrent();
    app(CreateTicket::class)(new CreateTicketDTO('secret', 'body', $customer->id));

    Tenant::forgetCurrent();

    expect(DB::table('tickets')->count())->toBe(0);
});

it('refuses to write a row tagged for a different tenant than the current one', function (): void {
    $tenantA = Tenant::create(['name' => 'A', 'domain' => 'a.test', 'status' => 'active']);
    $tenantB = Tenant::create(['name' => 'B', 'domain' => 'b.test', 'status' => 'active']);
    $customerB = User::factory()->create(['tenant_id' => $tenantB->id]);

    $tenantA->makeCurrent();

    // Current tenant is A; forging a row tagged tenant B violates the RLS
    // WITH CHECK constraint at the database engine, not just in the app.
    expect(fn () => DB::table('tickets')->insert([
        'ulid' => (string) Str::ulid(),
        'tenant_id' => $tenantB->id,
        'customer_id' => $customerB->id,
        'title' => 'forged',
        'description' => 'body',
        'status' => 'open',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});
