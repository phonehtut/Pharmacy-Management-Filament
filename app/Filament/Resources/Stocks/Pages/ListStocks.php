<?php

namespace App\Filament\Resources\Stocks\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Filament\Resources\Stocks\StockResource;
use App\Filament\Resources\StockTransfers\StockTransferResource;
use App\Models\Stock;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restock_now')
                ->label('Create Restock Purchase')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->url(fn (): string => PurchaseResource::getUrl('create')),
            Action::make('transfer_history')
                ->label('Transfer History')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->url(fn (): string => StockTransferResource::getUrl('index')),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $lowStockCount = Stock::query()
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10)
            ->count();
        $outOfStockCount = Stock::query()
            ->where('quantity', 0)
            ->count();
        $expiringSoonCount = Stock::query()
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [today(), today()->copy()->addDays(30)])
            ->count();

        return [
            'low_stock' => Tab::make('Low Stock')
                ->badge($lowStockCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('quantity', '>', 0)->where('quantity', '<=', 10)),
            'out_of_stock' => Tab::make('Out of Stock')
                ->badge($outOfStockCount)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('quantity', 0)),
            'expiring_soon' => Tab::make('Expiring 30 Days')
                ->badge($expiringSoonCount)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereBetween('expiry_date', [today(), today()->copy()->addDays(30)])),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'low_stock';
    }
}
