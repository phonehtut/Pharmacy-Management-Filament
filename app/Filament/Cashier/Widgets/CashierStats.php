<?php

namespace App\Filament\Cashier\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashierStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant?->getKey();

        if (! $tenantId) {
            return [
                Stat::make('Today Sales', '0'),
                Stat::make('Today Revenue', '0.00 MMK'),
                Stat::make('Items Sold', '0'),
            ];
        }

        $todaySalesQuery = Sale::query()
            ->where('branch_id', $tenantId)
            ->whereDate('sold_at', today());

        $todayRevenue = (float) (clone $todaySalesQuery)->sum('total');
        $todaySalesCount = (int) (clone $todaySalesQuery)->count();
        $todayItemsSold = (int) SaleItem::query()
            ->whereHas('sale', function ($query) use ($tenantId): void {
                $query
                    ->where('branch_id', $tenantId)
                    ->whereDate('sold_at', today());
            })
            ->sum('quantity');

        return [
            Stat::make('Today Sales', number_format($todaySalesCount))
                ->description('Total vouchers created today'),
            Stat::make('Today Revenue', number_format($todayRevenue, 2).' MMK')
                ->description('Gross sale amount (before discount/tax adjustment)'),
            Stat::make('Items Sold', number_format($todayItemsSold))
                ->description('Total unit quantity sold today'),
        ];
    }
}
