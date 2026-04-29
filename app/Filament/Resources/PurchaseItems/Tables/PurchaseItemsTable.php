<?php

namespace App\Filament\Resources\PurchaseItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase.invoice_no')
                    ->label('Invoice')
                    ->searchable(),
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('buy_price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('batch_no')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('purchase')
                    ->relationship('purchase', 'invoice_no'),
                SelectFilter::make('medicine')
                    ->relationship('medicine', 'name')
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->deferLoading()
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
