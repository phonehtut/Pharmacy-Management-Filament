<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Sale;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('user.name')
                    ->label('Cashier'),
                TextEntry::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                TextEntry::make('total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('discount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('tax')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('paid_amount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('change')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).' MMK'),
                TextEntry::make('sold_at')
                    ->dateTime(),
                RepeatableEntry::make('return_history')
                    ->label('Return History')
                    ->columnSpanFull()
                    ->placeholder('No return history yet.')
                    ->state(fn (Sale $record): array => DB::table('sale_item_returns as item_returns')
                        ->leftJoin('medicines as medicines', 'medicines.id', '=', 'item_returns.medicine_id')
                        ->leftJoin('users as users', 'users.id', '=', 'item_returns.returned_by')
                        ->where('item_returns.sale_id', $record->getKey())
                        ->orderByDesc('item_returns.returned_at')
                        ->get([
                            'item_returns.returned_at',
                            'medicines.name as medicine_name',
                            'item_returns.quantity',
                            'users.name as returned_by_name',
                            'item_returns.reason',
                        ])
                        ->map(fn (object $itemReturn): array => [
                            'returned_at' => $itemReturn->returned_at,
                            'medicine_name' => $itemReturn->medicine_name ?? 'Unknown',
                            'quantity' => (int) $itemReturn->quantity,
                            'returned_by_name' => $itemReturn->returned_by_name ?? 'System',
                            'reason' => filled($itemReturn->reason) ? $itemReturn->reason : null,
                        ])
                        ->all())
                    ->table([
                        TableColumn::make('Returned At'),
                        TableColumn::make('Medicine'),
                        TableColumn::make('Qty'),
                        TableColumn::make('Returned By'),
                        TableColumn::make('Reason'),
                    ])
                    ->schema([
                        TextEntry::make('returned_at')
                            ->label('Returned At')
                            ->dateTime(),
                        TextEntry::make('medicine_name')
                            ->label('Medicine'),
                        TextEntry::make('quantity')
                            ->label('Qty'),
                        TextEntry::make('returned_by_name')
                            ->label('Returned By'),
                        TextEntry::make('reason')
                            ->placeholder('-'),
                    ]),
            ])
            ->columns(3);
    }
}
