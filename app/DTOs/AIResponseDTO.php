<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSentiment;

final readonly class AIResponseDTO
{
    public function __construct(
        public TicketCategory $category,
        public TicketSentiment $sentiment,
        public TicketPriority $priority,
        public string $draftReply,
    ) {}

    public static function fallback(): self
    {
        return new self(
            category: TicketCategory::General,
            sentiment: TicketSentiment::Neutral,
            priority: TicketPriority::Medium,
            draftReply: 'Thank you for reaching out. A member of our support team will review your request and follow up shortly.',
        );
    }
}
