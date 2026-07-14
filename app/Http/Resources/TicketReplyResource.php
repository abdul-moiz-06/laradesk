<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketReply
 */
final class TicketReplyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'body' => $this->body,
            'author' => $this->whenLoaded('author', fn (): string => $this->author->name),
            'created_at' => $this->created_at,
        ];
    }
}
