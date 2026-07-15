<?php

declare(strict_types=1);

namespace App\Filament\Super\Resources\Tenants\Schemas;

use App\Enums\TenantStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('domain')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('plan')
                    ->maxLength(255),
                Select::make('status')
                    ->options(self::statusOptions())
                    ->default(TenantStatus::Pending->value)
                    ->required(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        $options = [];

        foreach (TenantStatus::cases() as $status) {
            $options[$status->value] = ucfirst($status->value);
        }

        return $options;
    }
}
