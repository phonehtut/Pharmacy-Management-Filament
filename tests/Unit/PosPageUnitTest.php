<?php

use App\Filament\Cashier\Pages\Pos;
use App\Models\Stock;

test('it normalizes checkout items by merging same medicine and batch', function () {
    $page = new Pos;
    $page->items = [
        ['medicine_id' => 10, 'batch_no' => 'B1', 'quantity' => 2, 'price' => 1500],
        ['medicine_id' => 10, 'batch_no' => 'B1', 'quantity' => 3, 'price' => 1500],
        ['medicine_id' => 10, 'batch_no' => 'B2', 'quantity' => 1, 'price' => 1700],
        ['medicine_id' => null, 'batch_no' => null, 'quantity' => 1, 'price' => 0],
    ];

    $normalizedItems = invokePrivateMethod($page, 'normalizeCheckoutItems');

    expect($normalizedItems)->toHaveCount(2)
        ->and($normalizedItems[0])->toMatchArray([
            'medicine_id' => 10,
            'batch_no' => 'B1',
            'quantity' => 5,
            'price' => 1500.0,
        ])
        ->and($normalizedItems[1])->toMatchArray([
            'medicine_id' => 10,
            'batch_no' => 'B2',
            'quantity' => 1,
            'price' => 1700.0,
        ]);
});

test('it appends stock to cart and increments existing line for same medicine and batch', function () {
    $page = new Pos;
    $page->items = [
        ['medicine_id' => null, 'batch_no' => null, 'quantity' => 1, 'price' => 0],
    ];

    $stock = new Stock([
        'medicine_id' => 22,
        'batch_no' => 'LOT-22',
        'sell_price' => 2500,
    ]);

    invokePrivateMethod($page, 'appendStockToCart', [$stock]);
    invokePrivateMethod($page, 'appendStockToCart', [$stock]);

    expect($page->items)->toHaveCount(1)
        ->and($page->items[0])->toMatchArray([
            'medicine_id' => 22,
            'batch_no' => 'LOT-22',
            'quantity' => 2,
            'price' => 2500.0,
        ]);
});

test('it normalizes barcode input by removing spaces and new lines', function () {
    $page = new Pos;

    $normalized = invokePrivateMethod($page, 'normalizeBarcodeInput', ["  89 01\r\n2\t3456  "]);

    expect($normalized)->toBe('890123456');
});

/**
 * @param  array<int, mixed>  $arguments
 */
function invokePrivateMethod(object $object, string $methodName, array $arguments = []): mixed
{
    $reflectionMethod = new ReflectionMethod($object, $methodName);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invokeArgs($object, $arguments);
}
