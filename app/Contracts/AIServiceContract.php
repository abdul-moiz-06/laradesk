<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\AIResponseDTO;

interface AIServiceContract
{
    public function analyze(string $title, string $description): AIResponseDTO;
}
