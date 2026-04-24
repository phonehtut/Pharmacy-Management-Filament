<?php

namespace App\Filament\Resources\SaleItems\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sale.id')
                    ->label('Sale'),
                TextEntry::make('medicine.name')
                    ->label('Medicine'),
                TextEntry::make('quantity'),
                TextEntry::make('price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('batch_no'),
            ])
            ->columns(2);
    }
}
