<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketCategory: string
{
    case Billing = 'billing';
    case Technical = 'technical';
    case General = 'general';
}
