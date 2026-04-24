<?php

namespace App\Filament\Resources\PurchaseItems\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('purchase.invoice_no')
                    ->label('Invoice'),
                TextEntry::make('medicine.name')
                    ->label('Medicine'),
                TextEntry::make('quantity'),
                TextEntry::make('buy_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('expiry_date')
                    ->date(),
                TextEntry::make('batch_no'),
            ])
            ->columns(2);
    }
}
