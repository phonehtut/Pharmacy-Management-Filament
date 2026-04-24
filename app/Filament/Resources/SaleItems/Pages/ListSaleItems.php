<?php

namespace App\Filament\Resources\SaleItems\Pages;

use App\Filament\Resources\SaleItems\SaleItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSaleItems extends ListRecords
{
    protected static string $resource = SaleItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
