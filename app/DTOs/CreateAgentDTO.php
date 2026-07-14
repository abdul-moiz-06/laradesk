<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CreateAgentDTO
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}
}
