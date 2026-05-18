<?php

namespace App\Filament\Cashier\Pages;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Stock;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class Pos extends Page
{
    protected static ?string $navigationLabel = 'POS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 1;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.cashier.pages.pos';

    public ?int $customer_id = null;

    public string $productSearch = '';

    public string $selectedCategory = 'all';

    public string $barcodeInput = '';

    public float $discount = 0;

    public float $tax = 0;

    public float $paid_amount = 0;

    public bool $isCheckingOut = false;

    /**
     * @var array<int, array{medicine_id: int|null, batch_no: string|null, quantity: int, price: float}>
     */
    public array $items = [];

    public function mount(): void
    {
        if (! Auth::user()?->branch_id) {
            abort(403, 'Cashier user must be assigned to a branch.');
        }

        $this->items = [$this->emptyItem()];
    }

    public function addItem(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function clearCart(): void
    {
        $this->items = [$this->emptyItem()];
        $this->discount = 0;
        $this->tax = 0;
        $this->paid_amount = 0;
    }

    public function selectCategory(string $category): void
    {
        $this->selectedCategory = trim($category) !== '' ? $category : 'all';
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function incrementQuantity(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['quantity'] = max(1, ((int) $this->items[$index]['quantity']) + 1);
    }

    public function decrementQuantity(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $currentQuantity = (int) ($this->items[$index]['quantity'] ?? 1);
        $this->items[$index]['quantity'] = max(1, $currentQuantity - 1);
    }

    public function addProductToCart(int $medicineId): void
    {
        $stock = $this->resolveStock(medicineId: $medicineId, batchNo: null);

        if (! $stock) {
            Notification::make()
                ->warning()
                ->title('Out of stock')
                ->body('Selected product is not available in your branch.')
                ->send();

            return;
        }

        $this->appendStockToCart($stock);
    }

    public function addByBarcode(?string $barcode = null): void
    {
        $resolvedBarcode = $this->normalizeBarcodeInput((string) ($barcode ?? $this->barcodeInput));

        if ($resolvedBarcode === '') {
            return;
        }

        $stock = Stock::query()
            ->where('branch_id', $this->getTenantBranchId())
            ->where('expiry_date', '>=', today()->toDateString())
            ->where('quantity', '>', 0)
            ->whereHas('medicine', function ($query) use ($resolvedBarcode): void {
                $query->where('barcode', $resolvedBarcode);
            })
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->first();

        if (! $stock) {
            $this->addError('barcodeInput', 'Barcode not found in your branch stock.');

            Notification::make()
                ->danger()
                ->title('Barcode not found')
                ->body("No stock found for barcode {$resolvedBarcode}.")
                ->send();

            return;
        }

        $this->resetErrorBag('barcodeInput');
        $this->barcodeInput = '';
        $this->appendStockToCart($stock);
    }

    private function normalizeBarcodeInput(string $barcode): string
    {
        return preg_replace('/\s+/u', '', trim($barcode)) ?? '';
    }

    public function checkout(): void
    {
        if ($this->isCheckingOut) {
            return;
        }

        $this->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'discount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.batch_no' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $normalizedItems = $this->normalizeCheckoutItems();

        if ($normalizedItems === []) {
            $this->addError('items', 'Please add at least one valid item to checkout.');

            return;
        }

        $subtotal = $this->calculateSubtotal();
        $netPayable = max(0, $subtotal - $this->discount + $this->tax);
        $changeAmount = max(0, $this->paid_amount - $netPayable);

        if ($this->paid_amount < $netPayable) {
            $this->addError('paid_amount', 'Paid amount must cover the net payable total.');

            return;
        }

        $createdSaleId = null;
        $this->isCheckingOut = true;

        try {
            DB::transaction(function () use ($subtotal, $changeAmount, $normalizedItems, &$createdSaleId): void {
                $user = Auth::user();
                $branchId = $this->getTenantBranchId();

                $sale = Sale::query()->create([
                    'branch_id' => $branchId,
                    'user_id' => (int) $user?->getKey(),
                    'customer_id' => $this->customer_id,
                    'total' => $subtotal,
                    'discount' => $this->discount,
                    'tax' => $this->tax,
                    'paid_amount' => $this->paid_amount,
                    'change' => $changeAmount,
                    'sold_at' => now(),
                ]);
                $createdSaleId = (int) $sale->getKey();

                foreach ($normalizedItems as $item) {
                    $sale->items()->create([
                        'medicine_id' => (int) $item['medicine_id'],
                        'batch_no' => filled($item['batch_no']) ? (string) $item['batch_no'] : '',
                        'quantity' => max(1, (int) $item['quantity']),
                        'price' => max(0, (float) ($item['price'] ?? 0)),
                    ]);
                }
            }, attempts: 3);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            Notification::make()
                ->danger()
                ->title('Checkout failed')
                ->body('Please fix the highlighted fields and try again.')
                ->send();

            return;
        } finally {
            $this->isCheckingOut = false;
        }

        $this->resetForm();

        Notification::make()
            ->success()
            ->title('Sale completed')
            ->body('POS voucher was created successfully.')
            ->send();

        if ($createdSaleId) {
            $printUrl = URL::temporarySignedRoute(
                'cashier.sales.print-voucher',
                now()->addMinutes(5),
                ['sale' => $createdSaleId],
            );

            $this->dispatch('pos-sale-completed', printUrl: $printUrl);
        }
    }

    public function getHeading(): ?string
    {
        return null;
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getCustomerOptions(): array
    {
        return Customer::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function getMedicineOptions(): array
    {
        return Stock::query()
            ->join('medicines', 'medicines.id', '=', 'stocks.medicine_id')
            ->where('branch_id', $this->getTenantBranchId())
            ->where('expiry_date', '>=', today()->toDateString())
            ->where('quantity', '>', 0)
            ->groupBy('stocks.medicine_id', 'medicines.name')
            ->orderBy('medicines.name')
            ->get()
            ->pluck('medicines.name', 'stocks.medicine_id')
            ->toArray();
    }

    public function getCategoryOptions(): array
    {
        return Stock::query()
            ->join('medicines', 'medicines.id', '=', 'stocks.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->where('stocks.branch_id', $this->getTenantBranchId())
            ->where('stocks.expiry_date', '>=', today()->toDateString())
            ->where('quantity', '>', 0)
            ->select('categories.name')
            ->distinct()
            ->orderBy('categories.name')
            ->pluck('categories.name')
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProductCatalog(): array
    {
        $search = trim($this->productSearch);

        return Stock::query()
            ->join('medicines', 'medicines.id', '=', 'stocks.medicine_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->where('stocks.branch_id', $this->getTenantBranchId())
            ->where('stocks.expiry_date', '>=', today()->toDateString())
            ->where('quantity', '>', 0)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('medicines.name', 'like', '%'.$search.'%')
                        ->orWhere('medicines.generic_name', 'like', '%'.$search.'%')
                        ->orWhere('medicines.barcode', 'like', '%'.$search.'%');
                });
            })
            ->when($this->selectedCategory !== 'all', function ($query): void {
                $query->where('categories.name', $this->selectedCategory);
            })
            ->groupBy(
                'stocks.medicine_id',
                'medicines.name',
                'medicines.barcode',
                'categories.name',
            )
            ->select([
                'stocks.medicine_id',
                'medicines.name',
                'medicines.barcode',
                'categories.name as category_name',
            ])
            ->selectRaw('MIN(stocks.sell_price) as sell_price')
            ->selectRaw('SUM(stocks.quantity) as stock_quantity')
            ->orderBy('medicines.name')
            ->get()
            ->map(function ($product): array {
                return [
                    'medicine_id' => (int) $product->medicine_id,
                    'name' => (string) $product->name,
                    'barcode' => (string) ($product->barcode ?? '-'),
                    'category' => (string) ($product->category_name ?: 'Uncategorized'),
                    'sell_price' => (float) $product->sell_price,
                    'stock' => (int) $product->stock_quantity,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCartItemsForDisplay(): array
    {
        $medicineIds = collect($this->items)
            ->pluck('medicine_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $medicineNames = Medicine::query()
            ->whereIn('id', $medicineIds)
            ->pluck('name', 'id');

        return collect($this->items)
            ->map(function (array $item, int $index) use ($medicineNames): ?array {
                if (blank($item['medicine_id'])) {
                    return null;
                }

                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $price = max(0, (float) ($item['price'] ?? 0));

                return [
                    'index' => $index,
                    'medicine_name' => (string) ($medicineNames[(int) $item['medicine_id']] ?? 'Unknown'),
                    'quantity' => $quantity,
                    'price' => $price,
                    'line_total' => round($quantity * $price, 2),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentSalesHistory(int $limit = 12): array
    {
        return Sale::query()
            ->withCount('items')
            ->with(['customer', 'user'])
            ->where('branch_id', $this->getTenantBranchId())
            ->latest('sold_at')
            ->limit($limit)
            ->get()
            ->map(fn (Sale $sale): array => [
                'id' => (int) $sale->id,
                'customer' => (string) ($sale->customer?->name ?? 'Walk-in'),
                'cashier' => (string) ($sale->user?->name ?? '-'),
                'items_count' => (int) $sale->items_count,
                'total' => (float) $sale->total,
                'sold_at' => (string) $sale->sold_at?->format('Y-m-d H:i'),
            ])
            ->all();
    }

    public function getBatchOptionsForItem(int $index): array
    {
        $medicineId = (int) ($this->items[$index]['medicine_id'] ?? 0);

        if ($medicineId <= 0) {
            return [];
        }

        return Stock::query()
            ->where('branch_id', $this->getTenantBranchId())
            ->where('medicine_id', $medicineId)
            ->where('expiry_date', '>=', today()->toDateString())
            ->where('quantity', '>', 0)
            ->select('batch_no')
            ->distinct()
            ->orderBy('batch_no')
            ->pluck('batch_no', 'batch_no')
            ->all();
    }

    public function getSubtotalProperty(): float
    {
        return $this->calculateSubtotal();
    }

    public function getNetPayableProperty(): float
    {
        return max(0, $this->subtotal - $this->discount + $this->tax);
    }

    public function getChangeProperty(): float
    {
        return max(0, $this->paid_amount - $this->netPayable);
    }

    public function getTaxAmountProperty(): float
    {
        return max(0, $this->netPayable - $this->subtotal + $this->discount);
    }

    public function addPaidAmount(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->paid_amount = round(max(0, $this->paid_amount + $amount), 2);
    }

    public function setPaidToNetPayable(): void
    {
        $this->paid_amount = round(max(0, $this->netPayable), 2);
    }

    public function getCartLinesProperty(): int
    {
        return (int) collect($this->items)
            ->filter(fn (array $item): bool => filled($item['medicine_id']))
            ->count();
    }

    public function getCartQuantityProperty(): int
    {
        return (int) collect($this->items)
            ->filter(fn (array $item): bool => filled($item['medicine_id']))
            ->sum(fn (array $item): int => max(1, (int) ($item['quantity'] ?? 1)));
    }

    public function getOutstandingProperty(): float
    {
        return max(0, $this->netPayable - $this->paid_amount);
    }

    public function getCanCheckoutProperty(): bool
    {
        return ($this->cartLines > 0) && ($this->paid_amount >= $this->netPayable);
    }

    private function getTenantBranchId(): int
    {
        $tenantId = (int) (Filament::getTenant()?->getKey() ?? 0);

        if ($tenantId > 0) {
            return $tenantId;
        }

        return (int) (Auth::user()?->branch_id ?? 0);
    }

    private function resolveStock(int $medicineId, ?string $batchNo): ?Stock
    {
        $query = Stock::query()
            ->where('branch_id', $this->getTenantBranchId())
            ->where('medicine_id', $medicineId)
            ->where('expiry_date', '>=', today()->toDateString())
            ->where('quantity', '>', 0);

        if (filled($batchNo)) {
            $query->where('batch_no', $batchNo);
        }

        return $query
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->first();
    }

    private function calculateSubtotal(): float
    {
        return round(collect($this->items)->sum(function (array $item): float {
            return max(1, (int) ($item['quantity'] ?? 1)) * max(0, (float) ($item['price'] ?? 0));
        }), 2);
    }

    /**
     * @return array<int, array{medicine_id: int, batch_no: string|null, quantity: int, price: float}>
     */
    private function normalizeCheckoutItems(): array
    {
        return collect($this->items)
            ->filter(fn (array $item): bool => filled($item['medicine_id']))
            ->map(function (array $item): array {
                return [
                    'medicine_id' => (int) $item['medicine_id'],
                    'batch_no' => filled($item['batch_no']) ? (string) $item['batch_no'] : null,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                    'price' => max(0, (float) ($item['price'] ?? 0)),
                ];
            })
            ->groupBy(fn (array $item): string => $item['medicine_id'].'|'.($item['batch_no'] ?? ''))
            ->map(function ($lineItems): array {
                $firstItem = $lineItems->first();

                return [
                    'medicine_id' => (int) $firstItem['medicine_id'],
                    'batch_no' => $firstItem['batch_no'],
                    'quantity' => (int) $lineItems->sum('quantity'),
                    'price' => (float) $firstItem['price'],
                ];
            })
            ->values()
            ->all();
    }

    private function appendStockToCart(Stock $stock): void
    {
        foreach ($this->items as $index => $item) {
            if (((int) ($item['medicine_id'] ?? 0) === (int) $stock->medicine_id) && (($item['batch_no'] ?? null) === $stock->batch_no)) {
                $this->items[$index]['quantity'] = max(1, (int) $item['quantity']) + 1;

                return;
            }
        }

        if ((count($this->items) === 1) && blank($this->items[0]['medicine_id'])) {
            $this->items[0] = [
                'medicine_id' => (int) $stock->medicine_id,
                'batch_no' => (string) $stock->batch_no,
                'quantity' => 1,
                'price' => (float) $stock->sell_price,
            ];

            return;
        }

        $this->items[] = [
            'medicine_id' => (int) $stock->medicine_id,
            'batch_no' => (string) $stock->batch_no,
            'quantity' => 1,
            'price' => (float) $stock->sell_price,
        ];
    }

    /**
     * @return array{medicine_id: int|null, batch_no: string|null, quantity: int, price: float}
     */
    private function emptyItem(): array
    {
        return [
            'medicine_id' => null,
            'batch_no' => null,
            'quantity' => 1,
            'price' => 0,
        ];
    }

    private function resetForm(): void
    {
        $this->customer_id = null;
        $this->discount = 0;
        $this->tax = 0;
        $this->paid_amount = 0;
        $this->items = [$this->emptyItem()];
    }
}
