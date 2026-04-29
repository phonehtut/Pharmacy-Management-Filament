<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Actions\ReturnSaleAction;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class EditSale extends EditRecord
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
            ViewAction::make(),
            ReturnSaleAction::make(),
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
