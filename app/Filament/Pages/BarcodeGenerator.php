<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BarcodeGenerator extends Page
{
    protected static ?string $navigationLabel = 'Barcode Generator';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 50;

    protected ?string $heading = 'Barcode Generator';

    protected ?string $subheading = 'Generate and download printable barcodes for labels.';

    protected string $view = 'filament.pages.barcode-generator';

    public string $barcodeValue = '123456789012';

    public string $barcodeFormat = 'CODE128';

    public int $barcodeWidth = 2;

    public int $barcodeHeight = 80;

    public bool $showText = true;

    public function generate(): void
    {
        $validatedData = $this->validate([
            'barcodeValue' => ['required', 'string', 'max:64'],
            'barcodeFormat' => ['required', 'in:CODE128,CODE39,EAN13,EAN8,UPC,ITF14'],
            'barcodeWidth' => ['required', 'integer', 'min:1', 'max:4'],
            'barcodeHeight' => ['required', 'integer', 'min:40', 'max:200'],
            'showText' => ['boolean'],
        ]);

        if ($validatedData['barcodeFormat'] === 'CODE39') {
            $validatedData['barcodeValue'] = strtoupper($validatedData['barcodeValue']);
            $this->barcodeValue = $validatedData['barcodeValue'];
        }

        $formatValidationError = $this->validateValueForFormat(
            value: $validatedData['barcodeValue'],
            format: $validatedData['barcodeFormat'],
        );

        if ($formatValidationError !== null) {
            $this->addError('barcodeValue', $formatValidationError);

            Notification::make()
                ->danger()
                ->title('Invalid barcode value')
                ->body($formatValidationError)
                ->send();

            return;
        }

        $this->dispatch(
            'barcode-generated',
            value: $validatedData['barcodeValue'],
            format: $validatedData['barcodeFormat'],
            width: $validatedData['barcodeWidth'],
            height: $validatedData['barcodeHeight'],
            displayText: $validatedData['showText'],
        );

        Notification::make()
            ->success()
            ->title('Barcode generated')
            ->send();
    }

    private function validateValueForFormat(string $value, string $format): ?string
    {
        return match ($format) {
            'EAN13' => preg_match('/^\d{13}$/', $value) === 1
                ? null
                : 'EAN-13 format requires exactly 13 digits.',
            'EAN8' => preg_match('/^\d{8}$/', $value) === 1
                ? null
                : 'EAN-8 format requires exactly 8 digits.',
            'UPC' => preg_match('/^\d{12}$/', $value) === 1
                ? null
                : 'UPC format requires exactly 12 digits.',
            'ITF14' => preg_match('/^\d{14}$/', $value) === 1
                ? null
                : 'ITF-14 format requires exactly 14 digits.',
            'CODE39' => preg_match('/^[0-9A-Z .\-$\/+%]+$/', $value) === 1
                ? null
                : 'CODE39 format allows A-Z, 0-9, space, and symbols . - $ / + % only.',
            default => null,
        };
    }
}
