<?php

namespace App\Filament\Cashier\Widgets;

use App\Models\Sale;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSales extends TableWidget
{
    protected static ?string $heading = 'Recent Sales';

    public function table(Table $table): Table
    {
        $tenantId = Filament::getTenant()?->getKey();

        return $table
            ->query(fn (): Builder => Sale::query()
                ->with(['customer', 'user'])
                ->when($tenantId, fn (Builder $query): Builder => $query->where('branch_id', $tenantId))
                ->latest('sold_at'))
            ->columns([
                TextColumn::make('id')
                    ->label('Voucher')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                TextColumn::make('user.name')
                    ->label('Cashier'),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextColumn::make('sold_at')
                    ->dateTime(),
            ])
            ->paginated([10]);
    }
}
