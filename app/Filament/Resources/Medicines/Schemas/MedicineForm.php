<?php

namespace App\Filament\Resources\Medicines\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MedicineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('generic_name')
                    ->required()
                    ->maxLength(255),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('brand')
                    ->required()
                    ->maxLength(255),
                TextInput::make('strength')
                    ->required()
                    ->maxLength(255),
                Select::make('dosage_form')
                    ->options([
                        'tablet' => 'Tablet',
                        'capsule' => 'Capsule',
                        'syrup' => 'Syrup',
                        'injection' => 'Injection',
                        'cream' => 'Cream',
                    ])
                    ->required(),
                TextInput::make('barcode')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
