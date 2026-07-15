<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('priority')->badge()->placeholder('—'),
                        TextEntry::make('category')->badge()->placeholder('—'),
                        TextEntry::make('customer.name')->label('Customer'),
                        TextEntry::make('assignedAgent.name')->label('Assigned agent')->placeholder('Unassigned'),
                        TextEntry::make('description')->columnSpanFull(),
                        TextEntry::make('ai_draft_reply')->label('AI draft reply')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Conversation')
                    ->schema([
                        RepeatableEntry::make('publishedReplies')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('author.name')->label('By'),
                                TextEntry::make('created_at')->dateTime()->label('At'),
                                TextEntry::make('body')->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
