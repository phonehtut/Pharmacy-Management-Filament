<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Actions\ReturnSaleAction;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Html2MediaAction::make('printInvoice')
                ->label('Print Voucher')
                ->icon('heroicon-o-printer')
                ->filename(fn (): string => "sale-{$this->getRecord()->id}-voucher")
                ->margins(14, 14, 14, 14)
                ->preview()
                ->savePdf()
                ->content(function () {
                    $sale = $this->getRecord()->loadMissing([
                        'branch.pharmacy',
                        'user',
                        'customer',
                        'items.medicine',
                    ]);

                    return view('filament.invoices.sale-voucher', [
                        'sale' => $sale,
                    ]);
                }),
            ReturnSaleAction::make(),
            EditAction::make(),
        ];
    }
}
