<?php

declare(strict_types=1);

use Anthropic\Laravel\Facades\Anthropic;
use Anthropic\Responses\Messages\CreateResponse;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSentiment;
use App\Services\AIService;

it('maps a structured response into a DTO', function (): void {
    Anthropic::fake([
        CreateResponse::fake(['content' => [[
            'type' => 'text',
            'text' => json_encode([
                'category' => 'billing',
                'sentiment' => 'frustrated',
                'priority' => 'high',
                'draft_reply' => 'We are reviewing your billing issue.',
            ]),
        ]]]),
    ]);

    $dto = (new AIService)->analyze('Overcharged', 'I was billed twice.');

    expect($dto->category)->toBe(TicketCategory::Billing)
        ->and($dto->sentiment)->toBe(TicketSentiment::Frustrated)
        ->and($dto->priority)->toBe(TicketPriority::High)
        ->and($dto->draftReply)->toBe('We are reviewing your billing issue.');
});

it('falls back when the response is not valid triage data', function (): void {
    Anthropic::fake([
        CreateResponse::fake(['content' => [['type' => 'text', 'text' => 'not-json']]]),
    ]);

    $dto = (new AIService)->analyze('x', 'y');

    expect($dto->category)->toBe(TicketCategory::General)
        ->and($dto->sentiment)->toBe(TicketSentiment::Neutral)
        ->and($dto->priority)->toBe(TicketPriority::Medium);
});
