<?php

namespace App\Filament\Actions;

use App\Models\Purchase;
use App\Services\Inventory\InventoryFlowService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReturnPurchaseAction
{
    public static function make(string $name = 'return_purchase'): Action
    {
        return Action::make($name)
            ->label('Return Item')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Return Purchase Item')
            ->modalDescription('Select an item from this purchase and enter quantity to return.')
            ->form([
                Select::make('purchase_item_id')
                    ->label('Purchase Item')
                    ->required()
                    ->searchable()
                    ->options(function (Purchase $record): array {
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
            ->visible(fn (Purchase $record): bool => $record->items()->where('quantity', '>', 0)->exists())
            ->action(function (Purchase $record, array $data): void {
                try {
                    $purchaseItem = $record->items()
                        ->whereKey((int) ($data['purchase_item_id'] ?? 0))
                        ->first();

                    if (! $purchaseItem) {
                        throw ValidationException::withMessages([
                            'purchase_item_id' => 'Selected purchase item is invalid.',
                        ]);
                    }

                    app(InventoryFlowService::class)->returnPurchaseItem(
                        purchaseItem: $purchaseItem,
                        returnQuantity: (int) ($data['return_quantity'] ?? 0),
                        reason: $data['reason'] ?? null,
                        returnedBy: Auth::id(),
                    );
                } catch (ValidationException $exception) {
                    $message = (string) (collect($exception->errors())->flatten()->first() ?? 'Unable to process purchase return.');

                    Notification::make()
                        ->danger()
                        ->title('Purchase return failed')
                        ->body($message)
                        ->send();

                    return;
                }

                Notification::make()
                    ->success()
                    ->title('Purchase item returned successfully')
                    ->send();
            });
    }
}
