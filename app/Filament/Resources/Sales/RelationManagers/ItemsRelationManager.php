<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use App\Models\SaleItem;
use App\Models\Stock;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

                        $stock = $this->resolveBestStock(
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
                    ->searchable()
                    ->live()
                    ->options(fn (Get $get): array => $this->batchOptions((int) $get('medicine_id')))
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if (blank($state)) {
                            return;
                        }

                        $stock = $this->resolveBestStock(
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
                    ->helperText(fn (Get $get): ?string => $this->availableQuantityHint(
                        medicineId: (int) $get('medicine_id'),
                        batchNo: $get('batch_no'),
                    )),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('MMK'),
            ])
            ->columns(2);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('medicine.name')
                    ->label('Medicine'),
                TextEntry::make('quantity'),
                TextEntry::make('price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('batch_no'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_no')
            ->columns([
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('batch_no')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->databaseTransaction()
                    ->successNotificationTitle('Sale item added and stock deducted')
                    ->after(fn (SaleItem $record): mixed => $this->notifyLowStockAfterMutation($record)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->databaseTransaction()
                    ->successNotificationTitle('Sale item updated and stock adjusted')
                    ->after(fn (SaleItem $record): mixed => $this->notifyLowStockAfterMutation($record)),
                DeleteAction::make()
                    ->databaseTransaction()
                    ->successNotificationTitle('Sale item deleted and stock restored')
                    ->after(fn (SaleItem $record): mixed => $this->notifyLowStockAfterMutation($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private function batchOptions(int $medicineId): array
    {
        if ($medicineId <= 0) {
            return [];
        }

        return Stock::query()
            ->where('branch_id', (int) $this->getOwnerRecord()->branch_id)
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->pluck('batch_no', 'batch_no')
            ->all();
    }

    private function availableQuantityHint(int $medicineId, ?string $batchNo): ?string
    {
        if (($medicineId <= 0) || blank($batchNo)) {
            return null;
        }

        $quantity = Stock::query()
            ->where('branch_id', (int) $this->getOwnerRecord()->branch_id)
            ->where('medicine_id', $medicineId)
            ->where('batch_no', $batchNo)
            ->sum('quantity');

        return "Available quantity in selected batch: {$quantity}";
    }

    private function resolveBestStock(int $medicineId, ?string $batchNo): ?Stock
    {
        if ($medicineId <= 0) {
            return null;
        }

        $query = Stock::query()
            ->where('branch_id', (int) $this->getOwnerRecord()->branch_id)
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

    private function notifyLowStockAfterMutation(SaleItem $saleItem): void
    {
        $remainingQuantity = Stock::query()
            ->where('branch_id', (int) $this->getOwnerRecord()->branch_id)
            ->where('medicine_id', (int) $saleItem->medicine_id)
            ->sum('quantity');

        if ($remainingQuantity > 10) {
            return;
        }

        Notification::make()
            ->warning()
            ->title('Low stock alert')
            ->body("Remaining stock for {$saleItem->medicine->name} is {$remainingQuantity}. Please restock soon.")
            ->send();
    }
}
