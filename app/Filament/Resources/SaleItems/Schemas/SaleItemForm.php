<?php

namespace App\Filament\Resources\SaleItems\Schemas;

use App\Models\Sale;
use App\Models\Stock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SaleItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sale_id')
                    ->relationship('sale', 'id')
                    ->required()
                    ->live()
                    ->searchable()
                    ->preload(),
                Select::make('medicine_id')
                    ->relationship('medicine', 'name')
                    ->required()
                    ->live()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if (blank($state)) {
                            $set('batch_no', null);

                            return;
                        }

                        $stock = self::resolveBestStock(
                            saleId: (int) $get('sale_id'),
                            medicineId: (int) $state,
                            batchNo: null,
                        );

                        if (! $stock) {
                            return;
                        }

                        $set('batch_no', $stock->batch_no);

                        if (blank($get('price')) || (((float) $get('price')) <= 0)) {
                            $set('price', (float) $stock->sell_price);
                        }
                    }),
                Select::make('batch_no')
                    ->required()
                    ->live()
                    ->searchable()
                    ->options(fn (Get $get): array => self::batchOptions(
                        saleId: (int) $get('sale_id'),
                        medicineId: (int) $get('medicine_id'),
                    ))
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if (blank($state)) {
                            return;
                        }

                        $stock = self::resolveBestStock(
                            saleId: (int) $get('sale_id'),
                            medicineId: (int) $get('medicine_id'),
                            batchNo: $state,
                        );

                        if ($stock && (blank($get('price')) || (((float) $get('price')) <= 0))) {
                            $set('price', (float) $stock->sell_price);
                        }
                    }),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText(fn (Get $get): ?string => self::availableQuantityHint(
                        saleId: (int) $get('sale_id'),
                        medicineId: (int) $get('medicine_id'),
                        batchNo: $get('batch_no'),
                    )),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ])
            ->columns(2);
    }

    /**
     * @return array<string, string>
     */
    private static function batchOptions(int $saleId, int $medicineId): array
    {
        if (($saleId <= 0) || ($medicineId <= 0)) {
            return [];
        }

        $branchId = (int) Sale::query()->whereKey($saleId)->value('branch_id');
        if ($branchId <= 0) {
            return [];
        }

        return Stock::query()
            ->where('branch_id', $branchId)
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->pluck('batch_no', 'batch_no')
            ->all();
    }

    private static function availableQuantityHint(int $saleId, int $medicineId, ?string $batchNo): ?string
    {
        if (($saleId <= 0) || ($medicineId <= 0) || blank($batchNo)) {
            return null;
        }

        $branchId = (int) Sale::query()->whereKey($saleId)->value('branch_id');
        if ($branchId <= 0) {
            return null;
        }

        $quantity = Stock::query()
            ->where('branch_id', $branchId)
            ->where('medicine_id', $medicineId)
            ->where('batch_no', $batchNo)
            ->sum('quantity');

        return "Available quantity in selected batch: {$quantity}";
    }

    private static function resolveBestStock(int $saleId, int $medicineId, ?string $batchNo): ?Stock
    {
        if (($saleId <= 0) || ($medicineId <= 0)) {
            return null;
        }

        $branchId = (int) Sale::query()->whereKey($saleId)->value('branch_id');
        if ($branchId <= 0) {
            return null;
        }

        $query = Stock::query()
            ->where('branch_id', $branchId)
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', today());

        if (filled($batchNo)) {
            $query->where('batch_no', $batchNo);
        }

        return $query
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->first();
    }
}
