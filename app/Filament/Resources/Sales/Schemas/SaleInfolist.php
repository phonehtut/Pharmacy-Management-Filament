<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('user.name')
                    ->label('Cashier'),
                TextEntry::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                TextEntry::make('total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('discount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('tax')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('paid_amount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('change')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('sold_at')
                    ->dateTime(),
            ])
            ->columns(3);
    }
}
