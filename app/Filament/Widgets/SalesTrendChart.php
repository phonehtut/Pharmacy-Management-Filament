<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesTrendChart extends ChartWidget
{
    protected ?string $heading = 'Sales Last 14 Days';

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);

            $labels[] = $date->format('d M');
            $values[] = (float) Sale::query()
                ->whereDate('sold_at', $date)
                ->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sales (MMK)',
                    'data' => $values,
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
