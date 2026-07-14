<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    // Tickets are opened by customers (API/web), never created from the panel.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
