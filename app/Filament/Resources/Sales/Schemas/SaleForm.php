<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->readOnly()
                    ->helperText('Auto-calculated from sale items.'),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set(
                        'change',
                        max(0, ((float) ($get('paid_amount') ?? 0)) - ((float) ($get('total') ?? 0) - (float) ($get('discount') ?? 0) + (float) ($get('tax') ?? 0))),
                    )),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set(
                        'change',
                        max(0, ((float) ($get('paid_amount') ?? 0)) - ((float) ($get('total') ?? 0) - (float) ($get('discount') ?? 0) + (float) ($get('tax') ?? 0))),
                    )),
                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set(
                        'change',
                        max(0, ((float) ($get('paid_amount') ?? 0)) - ((float) ($get('total') ?? 0) - (float) ($get('discount') ?? 0) + (float) ($get('tax') ?? 0))),
                    )),
                TextInput::make('change')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->readOnly(),
                DateTimePicker::make('sold_at')
                    ->required()
                    ->seconds(false)
                    ->default(now()),
            ])
            ->columns(3);
    }
}
