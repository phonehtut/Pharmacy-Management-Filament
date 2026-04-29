<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Filament\Actions\ReturnSaleAction;
use App\Filament\Exports\SaleDateRangeExporter;
use App\Filament\Exports\SaleExporter;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Voucher')
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK')
                    ->sortable(),
                TextColumn::make('sold_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                SelectFilter::make('user')
                    ->relationship('user', 'name'),
                Filter::make('sold_between')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('sold_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('sold_at', '<=', $date));
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->deferLoading()
            ->recordActions([
                ViewAction::make(),
                ReturnSaleAction::make(),
                EditAction::make(),
                Html2MediaAction::make('print_invoice')
                    ->label('Voucher')
                    ->icon('heroicon-o-printer')
                    ->filename(fn ($record): string => "sale-{$record->id}-voucher")
                    ->margins(14, 14, 14, 14)
                    ->preview()
                    ->savePdf()
                    ->content(function ($record) {
                        $sale = $record->loadMissing([
                            'branch.pharmacy',
                            'user',
                            'customer',
                            'items.medicine',
                        ]);

                        return view('filament.invoices.sale-voucher', [
                            'sale' => $sale,
                        ]);
                    }),
                Action::make('add_items')
                    ->label('Items')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn ($record): string => SaleResource::getUrl('edit', ['record' => $record]).'#relationManager'),
            ])
            ->toolbarActions([
                ExportAction::make('export_date_range')
                    ->label('Date Range Export')
                    ->icon('heroicon-o-calendar-days')
                    ->exporter(SaleDateRangeExporter::class)
                    ->formats([ExportFormat::Xlsx])
                    ->fileName(function (Export $export): string {
                        $from = Carbon::parse($export->getOptions()['from'] ?? now())->toDateString();
                        $until = Carbon::parse($export->getOptions()['until'] ?? now())->toDateString();

                        return "sales-{$from}-to-{$until}";
                    })
                    ->modifyQueryUsing(function (Builder $query, array $options): Builder {
                        $from = Carbon::parse($options['from'])->toDateString();
                        $until = Carbon::parse($options['until'])->toDateString();

                        return $query
                            ->whereDate('sold_at', '>=', $from)
                            ->whereDate('sold_at', '<=', $until);
                    }),
                ExportAction::make('export_daily')
                    ->label('Daily Export')
                    ->icon('heroicon-o-calendar')
                    ->exporter(SaleExporter::class)
                    ->formats([ExportFormat::Xlsx])
                    ->fileName(fn (): string => 'sales-daily-'.now()->toDateString())
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereDate('sold_at', now()->toDateString())),
                BulkActionGroup::make([
                    BulkAction::make('safe_delete_sales')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            DB::transaction(function () use ($records): void {
                                $records->each(function ($sale): void {
                                    $sale->items()
                                        ->get()
                                        ->each(fn ($item): bool => $item->delete());

                                    $sale->delete();
                                });
                            }, attempts: 3);
                        })
                        ->successNotificationTitle('Sales deleted and stock restored'),
                ]),
            ]);
    }
}
