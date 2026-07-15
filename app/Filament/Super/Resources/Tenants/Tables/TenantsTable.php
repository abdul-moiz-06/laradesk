<?php

declare(strict_types=1);

namespace App\Filament\Super\Resources\Tenants\Tables;

use App\Actions\Tenants\ActivateTenant;
use App\Actions\Tenants\CreateTenantAdmin;
use App\Actions\Tenants\SuspendTenant;
use App\DTOs\CreateTenantAdminDTO;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('domain')->searchable(),
                TextColumn::make('plan')->placeholder('n/a'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                self::inviteAdminAction(),
                self::suspendAction(),
                self::activateAction(),
            ]);
    }

    private static function inviteAdminAction(): Action
    {
        return Action::make('inviteAdmin')
            ->label('Invite admin')
            ->icon('heroicon-o-user-plus')
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
            ])
            ->action(function (Tenant $record, array $data): void {
                $name = $data['name'] ?? '';
                $email = $data['email'] ?? '';

                app(CreateTenantAdmin::class)(new CreateTenantAdminDTO(
                    tenantId: $record->id,
                    name: is_string($name) ? $name : '',
                    email: is_string($email) ? $email : '',
                ));
            });
    }

    private static function suspendAction(): Action
    {
        return Action::make('suspend')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Tenant $record): bool => $record->status !== TenantStatus::Suspended)
            ->action(fn (Tenant $record) => app(SuspendTenant::class)($record));
    }

    private static function activateAction(): Action
    {
        return Action::make('activate')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Tenant $record): bool => $record->status === TenantStatus::Suspended)
            ->action(fn (Tenant $record) => app(ActivateTenant::class)($record));
    }
}
