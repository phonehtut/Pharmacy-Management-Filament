<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Filament\Actions\ReturnPurchaseAction;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_no')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('purchased_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name'),
                SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                Filter::make('purchased_between')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('purchased_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('purchased_at', '<=', $date));
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->deferLoading()
            ->recordActions([
                ViewAction::make(),
                ReturnPurchaseAction::make(),
                EditAction::make(),
                Html2MediaAction::make('print_invoice')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->filename(fn ($record): string => "purchase-{$record->id}-invoice")
                    ->margins(14, 14, 14, 14)
                    ->preview()
                    ->savePdf()
                    ->content(function ($record) {
                        $purchase = $record->loadMissing([
                            'branch.pharmacy',
                            'supplier',
                            'items.medicine',
                        ]);

                        return view('filament.invoices.purchase-invoice', [
                            'purchase' => $purchase,
                        ]);
                    }),
                Action::make('add_items')
                    ->label('Items')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn ($record): string => PurchaseResource::getUrl('edit', ['record' => $record]).'#relationManager'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('safe_delete_purchases')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            DB::transaction(function () use ($records): void {
                                $records->each(function ($purchase): void {
                                    $purchase->items()
                                        ->get()
                                        ->each(fn ($item): bool => $item->delete());

                                    $purchase->delete();
                                });
                            }, attempts: 3);
                        })
                        ->successNotificationTitle('Purchases deleted and stock adjusted'),
                ]),
            ]);
    }
}
