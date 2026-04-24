<?php

namespace App\Filament\Resources\Pharmacies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PharmacyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone'),
                TextEntry::make('license_no')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(2);
    }
}
