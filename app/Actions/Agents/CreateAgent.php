<?php

declare(strict_types=1);

namespace App\Actions\Agents;

use App\DTOs\CreateAgentDTO;
use App\Enums\Role;
use App\Events\AgentInvited;
use App\Models\Tenant;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Creates an agent inside the current tenant and invites them to set their own
 * password via a Filament reset link. The tenant is taken from context (never
 * user input), the role is fixed to Agent, and no plaintext password is handled.
 */
final class CreateAgent
{
    public function __construct(private readonly Hasher $hasher) {}

    public function __invoke(CreateAgentDTO $data): User
    {
        $tenant = Tenant::current();

        if (! $tenant instanceof Tenant) {
            throw new RuntimeException('An agent can only be created within a tenant context.');
        }

        $agent = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $this->hasher->make(Str::random(64)),
            'tenant_id' => $tenant->getKey(),
        ]);

        $agent->assignRole(Role::Agent->value);

        $token = Password::createToken($agent);
        $notification = new ResetPassword($token);
        $notification->url = Filament::getResetPasswordUrl($token, $agent);
        $agent->notify($notification);

        AgentInvited::dispatch($agent);

        return $agent;
    }
}
