<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('invoice_no')
                    ->required()
                    ->maxLength(255)
                    ->default(fn (): string => 'PO-'.now()->format('ymdHis'))
                    ->unique(ignoreRecord: true),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->readOnly()
                    ->helperText('Auto-calculated from purchase items.'),
                DateTimePicker::make('purchased_at')
                    ->required()
                    ->seconds(false)
                    ->default(now()),
            ])
            ->columns(2);
    }
}
