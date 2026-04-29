<?php

namespace App\Filament\Exports;

use Filament\Forms\Components\DatePicker;

class SaleDateRangeExporter extends SaleExporter
{
    public static function getOptionsFormComponents(): array
    {
        return [
            DatePicker::make('from')
                ->label('From')
                ->required(),
            DatePicker::make('until')
                ->label('Until')
                ->required()
                ->afterOrEqual('from'),
        ];
    }
}
