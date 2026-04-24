<?php

namespace App\Filament\Resources\SaleItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SaleItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale.id')
                    ->label('Sale')
                    ->sortable(),
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
                SelectFilter::make('sale')
                    ->relationship('sale', 'id'),
                SelectFilter::make('medicine')
                    ->relationship('medicine', 'name')
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
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
