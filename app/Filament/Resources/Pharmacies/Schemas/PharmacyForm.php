<?php

namespace App\Filament\Resources\Pharmacies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PharmacyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->maxLength(255),
                TextInput::make('license_no')
                    ->maxLength(255),
                Textarea::make('address')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
