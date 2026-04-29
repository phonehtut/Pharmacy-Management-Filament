<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SaleVoucherPrintController extends Controller
{
    public function __invoke(Request $request, Sale $sale): View
    {
        $user = $request->user();

        abort_unless($user, 403);
        abort_unless((int) $user->branch_id === (int) $sale->branch_id, 403);

        $sale->loadMissing([
            'branch.pharmacy',
            'user',
            'customer',
            'items.medicine',
        ]);

        return view('filament.cashier.print.sale-voucher', [
            'sale' => $sale,
        ]);
    }
}
