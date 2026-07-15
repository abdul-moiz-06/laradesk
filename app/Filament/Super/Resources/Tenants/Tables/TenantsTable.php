<?php

declare(strict_types=1);

namespace App\Filament\Super\Resources\Tenants\Tables;

use App\Actions\Impersonation\StartImpersonation;
use App\Actions\Tenants\ActivateTenant;
use App\Actions\Tenants\CreateTenantAdmin;
use App\Actions\Tenants\SuspendTenant;
use App\DTOs\CreateTenantAdminDTO;
use App\Enums\Role;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                self::impersonateAction(),
                self::suspendAction(),
                self::activateAction(),
            ]);
    }

    private static function impersonateAction(): Action
    {
        return Action::make('impersonate')
            ->label('Impersonate')
            ->icon('heroicon-o-finger-print')
            ->color('warning')
            ->modalHeading('Impersonate a company user')
            ->modalDescription('You will sign in as the selected user. The session is time-boxed and every action is audited in this company\'s trail.')
            ->modalSubmitActionLabel('Start')
            ->visible(fn (Tenant $record): bool => $record->status !== TenantStatus::Suspended)
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->options(fn (Tenant $record): array => self::impersonatableUsers($record))
                    ->required(),
                Textarea::make('reason')
                    ->label('Reason for access')
                    ->required()
                    ->maxLength(500),
                TextInput::make('password')
                    ->label('Confirm your password')
                    ->password()
                    ->required()
                    ->rule('current_password'),
            ])
            ->action(function (array $data) {
                $userId = $data['user_id'] ?? null;
                $reason = $data['reason'] ?? null;

                $target = User::findOrFail(is_numeric($userId) ? (int) $userId : 0);

                app(StartImpersonation::class)($target, is_string($reason) ? $reason : '');

                return redirect()->to('/admin');
            });
    }

    /**
     * The company's onboarded staff (TenantAdmins and Agents who have completed
     * two-factor set-up) who may be impersonated, keyed by id for the select.
     *
     * @return array<int, string>
     */
    private static function impersonatableUsers(Tenant $tenant): array
    {
        $options = [];

        $users = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('app_authentication_secret')
            ->whereHas('roles', fn (Builder $query): Builder => $query->whereIn('name', [
                Role::TenantAdmin->value,
                Role::Agent->value,
            ]))
            ->orderBy('name')
            ->get(['id', 'name']);

        foreach ($users as $user) {
            $options[$user->id] = $user->name;
        }

        return $options;
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
