<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Portal\GenerateSupportLink;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Shows a TenantAdmin their company's public support link to copy onto their
 * own site. The link is signed and identifies the company by domain, so it is
 * tamper-proof.
 */
final class SupportLink extends Page
{
    public string $url = '';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Support link';

    protected static string|UnitEnum|null $navigationGroup = 'Support';

    protected string $view = 'filament.pages.support-link';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasRole(Role::TenantAdmin->value);
    }

    public function mount(): void
    {
        $tenant = Tenant::current();

        if ($tenant instanceof Tenant) {
            $this->url = app(GenerateSupportLink::class)($tenant);
        }
    }
}
