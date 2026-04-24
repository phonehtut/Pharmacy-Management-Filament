<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medicine_id')
                    ->relationship('medicine', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('type')
                    ->required()
                    ->options([
                        'in' => 'In',
                        'out' => 'Out',
                        'adjustment' => 'Adjustment',
                    ]),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Select::make('reference')
                    ->options([
                        'purchase' => 'Purchase',
                        'sale' => 'Sale',
                    ]),
            ])
            ->columns(2);
    }
}
