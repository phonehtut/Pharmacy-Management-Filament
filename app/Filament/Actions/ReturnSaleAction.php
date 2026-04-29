<?php

namespace App\Filament\Actions;

use App\Models\Sale;
use App\Services\Inventory\InventoryFlowService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReturnSaleAction
{
    public static function make(string $name = 'return_sale'): Action
    {
        return Action::make($name)
            ->label('Return Item')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Return Sale Item')
            ->modalDescription('Select an item from this sale and enter quantity to return.')
            ->form([
                Select::make('sale_item_id')
                    ->label('Sale Item')
                    ->required()
                    ->searchable()
                    ->options(function (Sale $record): array {
                        return $record->items()
                            ->with('medicine')
                            ->where('quantity', '>', 0)
                            ->orderBy('id')
                            ->get()
                            ->mapWithKeys(fn ($item): array => [
                                (string) $item->getKey() => sprintf(
                                    '%s | Batch: %s | Qty: %d',
                                    (string) ($item->medicine?->name ?? 'Unknown'),
                                    (string) $item->batch_no,
                                    (int) $item->quantity,
                                ),
                            ])
                            ->all();
                    }),
                TextInput::make('return_quantity')
                    ->label('Return Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Textarea::make('reason')
                    ->label('Reason')
                    ->rows(3)
                    ->maxLength(1000),
            ])
            ->visible(fn (Sale $record): bool => $record->items()->where('quantity', '>', 0)->exists())
            ->action(function (Sale $record, array $data): void {
                try {
                    $saleItem = $record->items()
                        ->whereKey((int) ($data['sale_item_id'] ?? 0))
                        ->first();

                    if (! $saleItem) {
                        throw ValidationException::withMessages([
                            'sale_item_id' => 'Selected sale item is invalid.',
                        ]);
                    }

                    app(InventoryFlowService::class)->returnSaleItem(
                        saleItem: $saleItem,
                        returnQuantity: (int) ($data['return_quantity'] ?? 0),
                        reason: $data['reason'] ?? null,
                        returnedBy: Auth::id(),
                    );
                } catch (ValidationException $exception) {
                    $message = (string) (collect($exception->errors())->flatten()->first() ?? 'Unable to process sale return.');

                    Notification::make()
                        ->danger()
                        ->title('Sale return failed')
                        ->body($message)
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title('Sale item returned successfully')
                    ->send();
            });
    }
}
