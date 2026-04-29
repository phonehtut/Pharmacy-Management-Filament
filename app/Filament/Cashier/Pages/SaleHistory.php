<?php

namespace App\Filament\Cashier\Pages;

use App\Filament\Actions\ReturnSaleAction;
use App\Models\Sale;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SaleHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Sale History';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 2;

    protected ?string $heading = 'Branch Sale History';

    protected string $view = 'filament.cashier.pages.sale-history';

    public function table(Table $table): Table
    {
        $tenantId = (int) (Filament::getTenant()?->getKey() ?? 0);

        return $table
            ->query(
                Sale::query()
                    ->with(['customer', 'user'])
                    ->withCount('items')
                    ->where('branch_id', $tenantId)
            )
            ->defaultSort('sold_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Voucher')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Cashier')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('discount')
                    ->money('MMK')
                    ->sortable(),
                TextColumn::make('tax')
                    ->money('MMK')
                    ->sortable(),
                TextColumn::make('total')
                    ->money('MMK')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('MMK')
                    ->sortable(),
                TextColumn::make('change')
                    ->money('MMK')
                    ->sortable(),
                TextColumn::make('sold_at')
                    ->label('Sold At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('sold_between')
                    ->form([
                        DatePicker::make('from')
                            ->label('From'),
                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('sold_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('sold_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ReturnSaleAction::make(),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
