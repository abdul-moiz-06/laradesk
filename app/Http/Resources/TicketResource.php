<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ticket
 */
final class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ulid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority?->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'replies' => TicketReplyResource::collection($this->whenLoaded('publishedReplies')),
        ];
    }
}
