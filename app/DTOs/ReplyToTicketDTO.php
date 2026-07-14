<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ReplyToTicketDTO
{
    public function __construct(
        public int $ticketId,
        public int $userId,
        public string $body,
    ) {}
}
