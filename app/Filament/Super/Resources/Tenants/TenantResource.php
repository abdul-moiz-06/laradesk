<?php

declare(strict_types=1);

namespace App\Filament\Super\Resources\Tenants;

use App\Filament\Super\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Super\Resources\Tenants\Pages\EditTenant;
use App\Filament\Super\Resources\Tenants\Pages\ListTenants;
use App\Filament\Super\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Super\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'company';

    protected static ?string $pluralModelLabel = 'companies';

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
