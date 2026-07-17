<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\DTOs\CreateTenantAdminDTO;
use App\DTOs\CreateTenantDTO;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Self-serve company sign-up: creates a new company and its first admin from
 * the public landing page, then invites that admin to set their own password.
 *
 * The company starts active but the admin cannot sign in until they set a
 * password from the emailed invite, so the invite doubles as email
 * verification. Returns the company, or null when the email is already in use.
 */
final class RegisterCompany
{
    public function __construct(
        private readonly CreateTenant $createTenant,
        private readonly CreateTenantAdmin $createTenantAdmin,
    ) {}

    public function __invoke(string $companyName, string $adminName, string $adminEmail): ?Tenant
    {
        $email = Str::lower(trim($adminEmail));

        if (User::query()->where('email', $email)->exists()) {
            return null;
        }

        /** @var Tenant $tenant */
        $tenant = DB::transaction(function () use ($companyName, $adminName, $email): Tenant {
            $tenant = ($this->createTenant)(new CreateTenantDTO(
                name: $companyName,
                domain: $this->uniqueDomain($companyName),
                status: TenantStatus::Active,
            ));

            ($this->createTenantAdmin)(new CreateTenantAdminDTO(
                tenantId: $tenant->id,
                name: $adminName,
                email: $email,
            ));

            return $tenant;
        });

        return $tenant;
    }

    /**
     * A clean, unique domain slug derived from the company name.
     */
    private function uniqueDomain(string $companyName): string
    {
        $base = Str::slug($companyName);
        $base = $base !== '' ? $base : 'company';

        $domain = $base;
        $suffix = 2;

        while (Tenant::query()->where('domain', $domain)->exists()) {
            $domain = $base.'-'.$suffix;
            $suffix++;
        }

        return $domain;
    }
}
