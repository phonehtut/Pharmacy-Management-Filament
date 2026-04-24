<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

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
                ->successNotificationTitle('Sale deleted and stock restored'),
        ];
    }
}
