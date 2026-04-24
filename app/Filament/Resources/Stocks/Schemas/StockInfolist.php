<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('medicine.name')
                    ->label('Medicine'),
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('batch_no'),
                TextEntry::make('expiry_date')
                    ->date(),
                TextEntry::make('quantity')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 10 ? 'warning' : 'success'),
                TextEntry::make('buy_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('sell_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(2);
    }
}
