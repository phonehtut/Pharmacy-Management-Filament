<?php

namespace App\Filament\Pharmacist\Widgets;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Stock;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PharmacistOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $branchId = $this->resolveBranchId();

        if ($branchId <= 0) {
            return [
                Stat::make('Today Sales', '0.00 MMK'),
                Stat::make('Today Purchases', '0.00 MMK'),
                Stat::make('Low Stock Alerts', '0'),
                Stat::make('Expiring In 30 Days', '0'),
            ];
        }

        $todaySales = (float) Sale::query()
            ->where('branch_id', $branchId)
            ->whereDate('sold_at', today())
            ->sum('total');

        $todayPurchases = (float) Purchase::query()
            ->where('branch_id', $branchId)
            ->whereDate('purchased_at', today())
            ->sum('total_amount');

        $lowStockCount = (int) Stock::query()
            ->where('branch_id', $branchId)
            ->where('quantity', '<=', 10)
            ->count();

        $expiringSoonCount = (int) Stock::query()
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [today(), today()->copy()->addDays(30)])
            ->count();

        return [
            Stat::make('Today Sales', number_format($todaySales, 2).' MMK')
                ->description('Current branch sales amount'),
            Stat::make('Today Purchases', number_format($todayPurchases, 2).' MMK')
                ->description('Today purchase total amount'),
            Stat::make('Low Stock Alerts', number_format($lowStockCount))
                ->description('Quantity <= 10')
                ->color($lowStockCount > 0 ? 'warning' : 'success'),
            Stat::make('Expiring In 30 Days', number_format($expiringSoonCount))
                ->description('Batches to prioritize')
                ->color($expiringSoonCount > 0 ? 'danger' : 'success'),
        ];
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
