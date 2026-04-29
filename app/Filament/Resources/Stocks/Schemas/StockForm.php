<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockForm
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
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('batch_no')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('expiry_date')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->disabledOn('edit'),
                TextInput::make('buy_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('sell_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ])
            ->columns(2);
    }
}
