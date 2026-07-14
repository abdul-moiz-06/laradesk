<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class StoreTicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $ticket = $this->route('ticket');

        return $user instanceof User
            && $ticket instanceof Ticket
            && $user->can('reply', $ticket);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
        ];
    }
}
