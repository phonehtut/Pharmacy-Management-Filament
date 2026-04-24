<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Stock;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $todaySales = (float) Sale::query()
            ->whereDate('sold_at', today())
            ->sum('total');
        $todayPurchases = (float) Purchase::query()
            ->whereDate('purchased_at', today())
            ->sum('total_amount');

        $salesChart = [];
        $purchaseChart = [];
        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            $salesChart[] = (float) Sale::query()->whereDate('sold_at', $date)->sum('total');
            $purchaseChart[] = (float) Purchase::query()->whereDate('purchased_at', $date)->sum('total_amount');
        }

        $lowStockCount = Stock::query()->where('quantity', '<=', 10)->count();
        $expiringSoonCount = Stock::query()
            ->whereBetween('expiry_date', [today(), today()->copy()->addDays(30)])
            ->count();

        return [
            Stat::make('Today Sales', number_format($todaySales, 2).' MMK')
                ->description('Last 7 days trend')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($salesChart)
                ->color('success'),
            Stat::make('Today Purchases', number_format($todayPurchases, 2).' MMK')
                ->description('Incoming stock value trend')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($purchaseChart)
                ->color('info'),
            Stat::make('Low Stock Alerts', (string) $lowStockCount)
                ->description('Stock quantity <= 10')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'warning' : 'success'),
            Stat::make('Expiring In 30 Days', (string) $expiringSoonCount)
                ->description('Batches to check soon')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoonCount > 0 ? 'danger' : 'success'),
            Stat::make('Master Data', Medicine::query()->count().' medicines / '.Customer::query()->count().' customers')
                ->description('Current inventory and customer base')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('primary'),
        ];
    }
}
