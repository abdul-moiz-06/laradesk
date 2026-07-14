<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Contracts\AIServiceContract;
use App\DTOs\AIResponseDTO;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSentiment;

final class FakeAIService implements AIServiceContract
{
    public function analyze(string $title, string $description): AIResponseDTO
    {
        return new AIResponseDTO(
            category: TicketCategory::Technical,
            sentiment: TicketSentiment::Neutral,
            priority: TicketPriority::Medium,
            draftReply: 'Thanks for reaching out — we are looking into this.',
        );
    }
}
