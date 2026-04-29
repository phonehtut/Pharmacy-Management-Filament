<?php

namespace App\Filament\Cashier\Pages;

use App\Filament\Actions\ReturnPurchaseAction;
use App\Models\Purchase;
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

class PurchaseHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Purchase History';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 3;

    protected ?string $heading = 'Branch Purchase History';

    protected string $view = 'filament.cashier.pages.purchase-history';

    public function table(Table $table): Table
    {
        $tenantId = (int) (Filament::getTenant()?->getKey() ?? 0);

        return $table
            ->query(
                Purchase::query()
                    ->with('supplier')
                    ->withCount('items')
                    ->where('branch_id', $tenantId)
            )
            ->defaultSort('purchased_at', 'desc')
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('MMK')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('purchased_at')
                    ->label('Purchased At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('purchased_between')
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
                                fn (Builder $query, string $date): Builder => $query->whereDate('purchased_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('purchased_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ReturnPurchaseAction::make(),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
