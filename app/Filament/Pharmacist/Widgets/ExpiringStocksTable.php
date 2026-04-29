<?php

namespace App\Filament\Pharmacist\Widgets;

use App\Filament\Resources\Stocks\StockResource;
use App\Models\Stock;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExpiringStocksTable extends TableWidget
{
    protected static ?string $heading = 'Expiring Stocks (Next 60 Days)';

    public function table(Table $table): Table
    {
        $branchId = $this->resolveBranchId();

        return $table
            ->query(
                fn (): Builder => Stock::query()
                    ->with('medicine')
                    ->where('branch_id', $branchId > 0 ? $branchId : -1)
                    ->where('quantity', '>', 0)
                    ->whereBetween('expiry_date', [today(), today()->copy()->addDays(60)])
                    ->orderBy('expiry_date'),
            )
            ->columns([
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable(),
                TextColumn::make('batch_no')
                    ->label('Batch'),
                TextColumn::make('quantity')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 10 ? 'warning' : 'success'),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('edit_stock')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Stock $record): string => StockResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultPaginationPageOption(10);
    }

    private function resolveBranchId(): int
    {
        $tenantId = (int) (Filament::getTenant()?->getKey() ?? 0);

        if ($tenantId > 0) {
            return $tenantId;
        }

        return (int) (Auth::user()?->branch_id ?? 0);
    }
}
