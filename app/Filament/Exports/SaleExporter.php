<?php

namespace App\Filament\Exports;

use App\Models\Sale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class SaleExporter extends Exporter
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Voucher'),
            ExportColumn::make('branch.name')
                ->label('Branch'),
            ExportColumn::make('user.name')
                ->label('Cashier'),
            ExportColumn::make('customer.name')
                ->label('Customer')
                ->formatStateUsing(fn (?string $state): string => filled($state) ? $state : 'Walk-in'),
            ExportColumn::make('items_count')
                ->label('Items')
                ->counts('items'),
            ExportColumn::make('total'),
            ExportColumn::make('discount'),
            ExportColumn::make('tax'),
            ExportColumn::make('paid_amount')
                ->label('Paid Amount'),
            ExportColumn::make('change'),
            ExportColumn::make('sold_at')
                ->formatStateUsing(fn (mixed $state): ?string => blank($state) ? null : Carbon::parse($state)->format('Y-m-d H:i:s')),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'branch',
            'user',
            'customer',
        ]);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
