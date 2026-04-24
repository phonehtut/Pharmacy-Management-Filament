<?php

namespace App\Filament\Resources\Sales\Tables;

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

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Voucher')
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('sold_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                SelectFilter::make('user')
                    ->relationship('user', 'name'),
                Filter::make('sold_between')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('sold_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('sold_at', '<=', $date));
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('add_items')
                    ->label('Items')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn ($record): string => \App\Filament\Resources\Sales\SaleResource::getUrl('edit', ['record' => $record]).'#relationManager'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('safe_delete_sales')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            DB::transaction(function () use ($records): void {
                                $records->each(function ($sale): void {
                                    $sale->items()
                                        ->get()
                                        ->each(fn ($item): bool => $item->delete());

                                    $sale->delete();
                                });
                            }, attempts: 3);
                        })
                        ->successNotificationTitle('Sales deleted and stock restored'),
                ]),
            ]);
    }
}
