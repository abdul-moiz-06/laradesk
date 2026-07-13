<?php

declare(strict_types=1);

namespace App\Services;

use Anthropic\Laravel\Facades\Anthropic;
use Anthropic\Responses\Messages\CreateResponse;
use App\Contracts\AIServiceContract;
use App\DTOs\AIResponseDTO;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSentiment;
use Illuminate\Support\Facades\Config;
use Throwable;

final class AIService implements AIServiceContract
{
    private const string SYSTEM_PROMPT = <<<'PROMPT'
        You are a support-desk triage assistant. Given a customer's support ticket,
        classify it and draft a reply for a human agent. Base every field only on the
        ticket content.
          - category: the ticket's topic.
          - sentiment: the customer's tone.
          - priority: how urgent it is.
          - draft_reply: a concise, professional reply the agent can send or edit.
        PROMPT;

    public function analyze(string $title, string $description): AIResponseDTO
    {
        try {
            /** @var CreateResponse $response */
            $response = retry(
                3,
                fn (): CreateResponse => Anthropic::messages()->create($this->payload($title, $description)),
                fn (int $attempt): int => (2 ** $attempt) * 100,
            );
        } catch (Throwable $e) {
            report($e);

            return AIResponseDTO::fallback();
        }

        return $this->toDTO($response) ?? AIResponseDTO::fallback();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $title, string $description): array
    {
        return [
            'model' => Config::string('anthropic.model', 'claude-haiku-4-5'),
            'max_tokens' => 1024,
            'system' => [[
                'type' => 'text',
                'text' => self::SYSTEM_PROMPT,
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'output_config' => [
                'format' => [
                    'type' => 'json_schema',
                    'schema' => $this->schema(),
                ],
            ],
            'messages' => [[
                'role' => 'user',
                'content' => "Title: {$title}\n\nDescription:\n{$description}",
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['category', 'sentiment', 'priority', 'draft_reply'],
            'properties' => [
                'category' => ['type' => 'string', 'enum' => array_map(fn (TicketCategory $c): string => $c->value, TicketCategory::cases())],
                'sentiment' => ['type' => 'string', 'enum' => array_map(fn (TicketSentiment $s): string => $s->value, TicketSentiment::cases())],
                'priority' => ['type' => 'string', 'enum' => array_map(fn (TicketPriority $p): string => $p->value, TicketPriority::cases())],
                'draft_reply' => ['type' => 'string'],
            ],
        ];
    }

    private function toDTO(CreateResponse $response): ?AIResponseDTO
    {
        $text = null;
        foreach ($response->content as $block) {
            if ($block->type === 'text' && $block->text !== null) {
                $text = $block->text;
                break;
            }
        }

        if ($text === null) {
            return null;
        }

        $data = json_decode($text, true);
        if (! is_array($data)) {
            return null;
        }

        $categoryValue = $data['category'] ?? null;
        $sentimentValue = $data['sentiment'] ?? null;
        $priorityValue = $data['priority'] ?? null;
        $draftReply = $data['draft_reply'] ?? null;

        if (! is_string($categoryValue) || ! is_string($sentimentValue) || ! is_string($priorityValue) || ! is_string($draftReply)) {
            return null;
        }

        $category = TicketCategory::tryFrom($categoryValue);
        $sentiment = TicketSentiment::tryFrom($sentimentValue);
        $priority = TicketPriority::tryFrom($priorityValue);

        if ($category === null || $sentiment === null || $priority === null) {
            return null;
        }

        return new AIResponseDTO($category, $sentiment, $priority, $draftReply);
    }
}
