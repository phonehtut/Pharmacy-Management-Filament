<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class SalesTrendChart extends ChartWidget
{
    protected ?string $heading = 'Sales Last 14 Days';

    protected ?string $maxHeight = '320px';

    protected string $view = 'filament.widgets.sales-trend-chart';

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

    public function exportMonthlySalesXlsx()
    {
        $sales = Sale::query()
            ->with(['branch', 'user', 'customer'])
            ->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderBy('sold_at')
            ->get();

        $filename = 'monthly-sales-'.now()->format('Y-m').'.xlsx';
        $tempPath = storage_path('app/'.$filename);

        $writer = new Writer;
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues([
            'Voucher',
            'Date',
            'Branch',
            'Cashier',
            'Customer',
            'Total (MMK)',
            'Paid (MMK)',
            'Change (MMK)',
        ]));

        foreach ($sales as $sale) {
            $writer->addRow(Row::fromValues([
                $sale->id,
                $sale->sold_at?->format('Y-m-d H:i'),
                $sale->branch?->name,
                $sale->user?->name,
                $sale->customer?->name ?? 'Walk-in',
                (float) $sale->total,
                (float) $sale->paid_amount,
                (float) $sale->change,
            ]));
        }

        $writer->close();

        return response()->download(
            $tempPath,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
}
