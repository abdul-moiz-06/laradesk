<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets\Tables;

use App\Actions\Tickets\AssignTicket;
use App\Actions\Tickets\CloseTicket;
use App\Actions\Tickets\ReplyToTicket;
use App\DTOs\ReplyToTicketDTO;
use App\Enums\Role;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('status')->badge(),
                TextColumn::make('priority')->badge()->placeholder('—'),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('assignedAgent.name')->label('Agent')->placeholder('Unassigned'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::enumOptions(TicketStatus::cases())),
                SelectFilter::make('priority')->options(self::enumOptions(TicketPriority::cases())),
            ])
            ->recordActions([
                ViewAction::make(),
                self::replyAction(),
                self::assignAction(),
                self::closeAction(),
            ]);
    }

    private static function replyAction(): Action
    {
        return Action::make('reply')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->visible(fn (Ticket $record): bool => Auth::user()?->can('reply', $record) ?? false)
            ->schema([
                Textarea::make('body')->label('Reply')->required(),
            ])
            ->action(function (Ticket $record, array $data): void {
                /** @var User $user */
                $user = Auth::user();
                $body = $data['body'] ?? '';

                app(ReplyToTicket::class)(new ReplyToTicketDTO(
                    ticketId: $record->id,
                    userId: $user->id,
                    body: is_string($body) ? $body : '',
                ));
            });
    }

    private static function assignAction(): Action
    {
        return Action::make('assign')
            ->icon('heroicon-o-user-plus')
            ->visible(fn (Ticket $record): bool => Auth::user()?->can('assign', $record) ?? false)
            ->schema([
                Select::make('agent_id')->label('Agent')->options(self::tenantAgents(...))->required(),
            ])
            ->action(function (Ticket $record, array $data): void {
                $agent = User::query()->whereKey($data['agent_id'] ?? null)->first();

                if ($agent instanceof User) {
                    app(AssignTicket::class)($record, $agent);
                }
            });
    }

    private static function closeAction(): Action
    {
        return Action::make('close')
            ->icon('heroicon-o-check-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Ticket $record): bool => ($record->status !== TicketStatus::Closed)
                && (Auth::user()?->can('close', $record) ?? false))
            ->action(function (Ticket $record): void {
                app(CloseTicket::class)($record);
            });
    }

    /**
     * The agents of the current tenant. Users carry no global scope, so this
     * query is explicitly tenant-scoped to prevent cross-tenant leakage.
     *
     * @return array<int|string, string>
     */
    private static function tenantAgents(): array
    {
        $agents = User::query()
            ->where('tenant_id', Tenant::current()?->getKey())
            ->get()
            ->filter(fn (User $user): bool => $user->hasRole(Role::Agent->value));

        $options = [];

        foreach ($agents as $agent) {
            $options[$agent->id] = $agent->name;
        }

        return $options;
    }

    /**
     * @param  array<int, TicketStatus|TicketPriority>  $cases
     * @return array<string, string>
     */
    private static function enumOptions(array $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $options[$case->value] = ucfirst($case->value);
        }

        return $options;
    }
}
