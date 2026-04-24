<?php

namespace App\Filament\Resources\StockTransfers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transferred_at')
                    ->label('Transferred At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromBranch.name')
                    ->label('From Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('toBranch.name')
                    ->label('To Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch_no')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('transferredBy.name')
                    ->label('Transferred By')
                    ->placeholder('System')
                    ->searchable(),
                TextColumn::make('note')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('medicine')
                    ->relationship('medicine', 'name')
                    ->searchable(),
                SelectFilter::make('from_branch_id')
                    ->label('From Branch')
                    ->relationship('fromBranch', 'name')
                    ->searchable(),
                SelectFilter::make('to_branch_id')
                    ->label('To Branch')
                    ->relationship('toBranch', 'name')
                    ->searchable(),
                Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('transferred_at', today())),
            ])
            ->defaultSort('transferred_at', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }
}
