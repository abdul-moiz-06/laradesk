<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit entry')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('When')->dateTime(),
                        TextEntry::make('event')->label('Action')->badge(),
                        TextEntry::make('causer.name')->label('By')->placeholder('System'),
                        TextEntry::make('subject_type')
                            ->label('Subject')
                            ->formatStateUsing(fn (?string $state): string => class_basename($state ?? ''))
                            ->placeholder('n/a'),
                        TextEntry::make('description')->columnSpanFull(),
                        KeyValueEntry::make('properties')->label('Details')->columnSpanFull(),
                    ]),
            ]);
    }
}
