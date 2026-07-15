<?php

declare(strict_types=1);

namespace App\Filament\Super\Resources\Tenants\Pages;

use App\Actions\Tenants\CreateTenant as CreateTenantAction;
use App\DTOs\CreateTenantDTO;
use App\Enums\TenantStatus;
use App\Events\TenantCreated;
use App\Filament\Super\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $name = $data['name'] ?? '';
        $domain = $data['domain'] ?? '';
        $plan = $data['plan'] ?? null;
        $status = $data['status'] ?? '';

        $tenant = app(CreateTenantAction::class)(new CreateTenantDTO(
            name: is_string($name) ? $name : '',
            domain: is_string($domain) ? $domain : '',
            plan: is_string($plan) ? $plan : null,
            status: (is_string($status) ? TenantStatus::tryFrom($status) : null) ?? TenantStatus::Pending,
        ));

        $tenant->makeCurrent();
        TenantCreated::dispatch($tenant);

        return $tenant;
    }
}
