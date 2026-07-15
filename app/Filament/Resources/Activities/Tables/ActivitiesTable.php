<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
                TextColumn::make('event')->label('Action')->badge(),
                TextColumn::make('description')->label('Description'),
                TextColumn::make('causer.name')->label('By')->placeholder('System'),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state): string => class_basename($state ?? ''))
                    ->placeholder('n/a'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
