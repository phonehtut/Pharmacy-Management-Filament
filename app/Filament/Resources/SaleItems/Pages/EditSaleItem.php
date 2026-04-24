<?php

namespace App\Filament\Resources\SaleItems\Pages;

use App\Filament\Resources\SaleItems\SaleItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSaleItem extends EditRecord
{
    protected static string $resource = SaleItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
