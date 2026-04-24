<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone'),
                TextEntry::make('email'),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(2);
    }
}
