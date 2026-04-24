<?php

namespace App\Filament\Resources\PurchaseItems\Schemas;

use App\Models\Purchase;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('purchase_id')
                    ->relationship('purchase', 'invoice_no')
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
                            return;
                        }

                        $latestStock = self::resolveLatestStock(
                            purchaseId: (int) $get('purchase_id'),
                            medicineId: (int) $state,
                        );

                        if ($latestStock && blank($get('buy_price'))) {
                            $set('buy_price', (float) $latestStock->buy_price);
                        }

                        if ($latestStock && blank($get('expiry_date'))) {
                            $set('expiry_date', $latestStock->expiry_date?->copy()->addMonths(6)?->toDateString());
                        }

                        if (blank($get('batch_no'))) {
                            $set('batch_no', 'PO-'.now()->format('ymd').'-'.random_int(1000, 9999));
                        }
                    }),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('buy_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DatePicker::make('expiry_date')
                    ->required()
                    ->default(now()->addYear()),
                TextInput::make('batch_no')
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    private static function resolveLatestStock(int $purchaseId, int $medicineId): ?Stock
    {
        if ($medicineId <= 0) {
            return null;
        }

        $branchId = (int) Purchase::query()->whereKey($purchaseId)->value('branch_id');

        return Stock::query()
            ->where('medicine_id', $medicineId)
            ->when($branchId > 0, fn ($query): mixed => $query->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->first();
    }
}
