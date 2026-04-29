<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Actions\ReturnPurchaseAction;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Html2MediaAction::make('printInvoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->filename(fn (): string => "purchase-{$this->getRecord()->id}-invoice")
                ->margins(14, 14, 14, 14)
                ->preview()
                ->savePdf()
                ->content(function () {
                    $purchase = $this->getRecord()->loadMissing([
                        'branch.pharmacy',
                        'supplier',
                        'items.medicine',
                    ]);

                    return view('filament.invoices.purchase-invoice', [
                        'purchase' => $purchase,
                    ]);
                }),
            ReturnPurchaseAction::make(),
            EditAction::make(),
        ];
    }
}
