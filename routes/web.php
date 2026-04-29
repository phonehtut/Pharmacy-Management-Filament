<?php

use App\Http\Controllers\Cashier\SaleVoucherPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'signed'])
    ->get('/cashier/sales/{sale}/print-voucher', SaleVoucherPrintController::class)
    ->name('cashier.sales.print-voucher');
