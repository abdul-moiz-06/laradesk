<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('create', Ticket::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }
}
