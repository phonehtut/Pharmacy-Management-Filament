<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Stocks\StockResource;
use App\Models\Stock;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockTable extends TableWidget
{
    protected static ?string $heading = 'Low Stock Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Stock::query()
                    ->with(['medicine', 'branch'])
                    ->where('quantity', '<=', 10)
                    ->orderBy('quantity'),
            )
            ->columns([
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),
                TextColumn::make('batch_no')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'warning')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                Filter::make('critical')
                    ->label('Critical (<= 5)')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '<=', 5)),
            ])
            ->headerActions([
                Action::make('view_stocks')
                    ->label('Open Stock Resource')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (): string => StockResource::getUrl('index')),
            ])
            ->recordActions([
                Action::make('edit_stock')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Stock $record): string => StockResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
