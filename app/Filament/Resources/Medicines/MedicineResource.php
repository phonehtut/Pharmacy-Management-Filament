<?php

namespace App\Filament\Resources\Medicines;

use App\Filament\Resources\Medicines\Pages\CreateMedicine;
use App\Filament\Resources\Medicines\Pages\EditMedicine;
use App\Filament\Resources\Medicines\Pages\ListMedicines;
use App\Filament\Resources\Medicines\Pages\ViewMedicine;
use App\Filament\Resources\Medicines\RelationManagers\StockMovementsRelationManager;
use App\Filament\Resources\Medicines\RelationManagers\StocksRelationManager;
use App\Filament\Resources\Medicines\Schemas\MedicineForm;
use App\Filament\Resources\Medicines\Schemas\MedicineInfolist;
use App\Filament\Resources\Medicines\Tables\MedicinesTable;
use App\Models\Medicine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MedicineForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicineInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicinesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StocksRelationManager::class,
            StockMovementsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicines::route('/'),
            'create' => CreateMedicine::route('/create'),
            'view' => ViewMedicine::route('/{record}'),
            'edit' => EditMedicine::route('/{record}/edit'),
        ];
    }
}
