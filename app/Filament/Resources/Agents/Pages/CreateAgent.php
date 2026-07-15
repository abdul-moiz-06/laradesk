<?php

declare(strict_types=1);

namespace App\Filament\Resources\Agents\Pages;

use App\Actions\Agents\CreateAgent as CreateAgentAction;
use App\DTOs\CreateAgentDTO;
use App\Filament\Resources\Agents\AgentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAgent extends CreateRecord
{
    protected static string $resource = AgentResource::class;

    /**
     * Route creation through the action so the agent is stamped with the current
     * tenant and the Agent role, and invited to set their own password.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';

        return app(CreateAgentAction::class)(new CreateAgentDTO(
            name: is_string($name) ? $name : '',
            email: is_string($email) ? $email : '',
        ));
    }
}
