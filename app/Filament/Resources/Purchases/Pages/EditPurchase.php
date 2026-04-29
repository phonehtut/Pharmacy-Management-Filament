<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Actions\ReturnPurchaseAction;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class EditPurchase extends EditRecord
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
            ViewAction::make(),
            ReturnPurchaseAction::make(),
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
