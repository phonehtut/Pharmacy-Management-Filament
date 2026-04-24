<?php

namespace App\Filament\Resources\Medicines\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MedicineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('generic_name'),
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('brand'),
                TextEntry::make('strength'),
                TextEntry::make('dosage_form')
                    ->badge(),
                TextEntry::make('barcode'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(2);
    }
}
