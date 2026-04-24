<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->databaseTransaction()
                ->before(function (): void {
                    $this->getRecord()
                        ->items()
                        ->get()
                        ->each(fn ($item): bool => $item->delete());
                })
                ->successNotificationTitle('Purchase deleted and stock adjusted'),
        ];
    }
}
