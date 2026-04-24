<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('invoice_no'),
                TextEntry::make('supplier.name')
                    ->label('Supplier'),
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('total_amount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('purchased_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(2);
    }
}
