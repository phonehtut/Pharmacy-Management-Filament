<?php

namespace App\Filament\Resources\Stocks\Tables;

use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Services\Inventory\InventoryFlowService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch_no')
                    ->searchable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 10 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('buy_price')
                    ->label('Buy')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('sell_price')
                    ->label('Sell')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                SelectFilter::make('medicine')
                    ->relationship('medicine', 'name')
                    ->searchable(),
                Filter::make('low_stock')
                    ->label('Low Stock (<= 10)')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '<=', 10)),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->whereDate('expiry_date', '<', today())),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('transfer_stock')
                    ->label('Transfer')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn (Stock $record): bool => (int) $record->quantity > 0)
                    ->form([
                        FormSelect::make('to_branch_id')
                            ->label('Transfer To Branch')
                            ->required()
                            ->searchable()
                            ->options(fn (Stock $record): array => Branch::query()
                                ->whereKeyNot($record->branch_id)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()),
                        FormTextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Stock $record): int => (int) $record->quantity)
                            ->default(fn (Stock $record): int => min(10, max(1, (int) $record->quantity)))
                            ->helperText(fn (Stock $record): string => "Available in source branch: {$record->quantity}"),
                        FormTextInput::make('to_batch_no')
                            ->label('Destination Batch No')
                            ->maxLength(255)
                            ->default(fn (Stock $record): string => (string) $record->batch_no),
                        FormTextarea::make('note')
                            ->rows(2)
                            ->maxLength(1000),
                    ])
                    ->action(function (Stock $record, array $data): void {
                        app(InventoryFlowService::class)->transferStock(
                            sourceStock: $record,
                            toBranchId: (int) $data['to_branch_id'],
                            quantity: (int) $data['quantity'],
                            toBatchNo: filled($data['to_batch_no'] ?? null) ? (string) $data['to_batch_no'] : null,
                            note: filled($data['note'] ?? null) ? (string) $data['note'] : null,
                            transferredBy: auth()->id() ? (int) auth()->id() : null,
                        );
                    })
                    ->successNotificationTitle('Stock transferred successfully'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('generate_restock_purchase')
                        ->label('Generate Restock Purchase')
                        ->icon('heroicon-o-truck')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            FormSelect::make('supplier_id')
                                ->label('Supplier')
                                ->required()
                                ->searchable()
                                ->options(fn (): array => Supplier::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all()),
                            FormTextInput::make('restock_quantity')
                                ->label('Restock Qty Per Item')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(30),
                            FormTextInput::make('markup_percent')
                                ->label('Markup %')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(20),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            if ($records->isEmpty()) {
                                return;
                            }

                            $branchIds = $records->pluck('branch_id')->unique();
                            if ($branchIds->count() > 1) {
                                throw ValidationException::withMessages([
                                    'supplier_id' => 'Please select stocks from a single branch to generate one purchase.',
                                ]);
                            }

                            DB::transaction(function () use ($records, $data, $branchIds): void {
                                $invoiceNo = 'RST-'.now()->format('ymdHis').'-'.random_int(100, 999);

                                $purchase = Purchase::query()->create([
                                    'supplier_id' => (int) $data['supplier_id'],
                                    'branch_id' => (int) $branchIds->first(),
                                    'invoice_no' => $invoiceNo,
                                    'total_amount' => 0,
                                    'purchased_at' => now(),
                                ]);

                                $totalAmount = 0.0;
                                $restockQuantity = (int) $data['restock_quantity'];
                                $markupPercent = ((float) $data['markup_percent']) / 100;

                                /** @var Stock $stock */
                                foreach ($records as $stock) {
                                    $buyPrice = (float) $stock->buy_price;
                                    $totalAmount += $buyPrice * $restockQuantity;
                                    $expiryDate = $stock->expiry_date && $stock->expiry_date->isFuture()
                                        ? $stock->expiry_date->copy()->addMonths(6)
                                        : now()->addYear();

                                    $purchaseItem = PurchaseItem::query()->create([
                                        'purchase_id' => $purchase->id,
                                        'medicine_id' => $stock->medicine_id,
                                        'quantity' => $restockQuantity,
                                        'buy_price' => $buyPrice,
                                        'expiry_date' => $expiryDate,
                                        'batch_no' => 'RST-'.$stock->medicine_id.'-'.now()->format('His').'-'.random_int(100, 999),
                                    ]);

                                    Stock::query()
                                        ->where('branch_id', (int) $branchIds->first())
                                        ->where('medicine_id', $stock->medicine_id)
                                        ->where('batch_no', $purchaseItem->batch_no)
                                        ->update([
                                            'sell_price' => round($buyPrice * (1 + $markupPercent), 2),
                                        ]);
                                }

                                $purchase->update(['total_amount' => round($totalAmount, 2)]);
                            }, attempts: 3);
                        })
                        ->successNotificationTitle('Restock purchase generated successfully'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
