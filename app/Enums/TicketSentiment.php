<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketSentiment: string
{
    case Frustrated = 'frustrated';
    case Neutral = 'neutral';
    case Satisfied = 'satisfied';
}
