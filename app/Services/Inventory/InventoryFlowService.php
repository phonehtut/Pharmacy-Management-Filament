<?php

namespace App\Services\Inventory;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryFlowService
{
    public function transferStock(
        Stock $sourceStock,
        int $toBranchId,
        int $quantity,
        ?string $toBatchNo = null,
        ?string $note = null,
        ?int $transferredBy = null,
    ): StockTransfer {
        if ($toBranchId <= 0) {
            throw ValidationException::withMessages([
                'to_branch_id' => 'Please select a valid destination branch.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Transfer quantity must be greater than zero.',
            ]);
        }

        $transfer = null;

        DB::transaction(function () use (
            $sourceStock,
            $toBranchId,
            $quantity,
            $toBatchNo,
            $note,
            $transferredBy,
            &$transfer,
        ): void {
            $lockedSource = Stock::query()
                ->whereKey($sourceStock->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedSource) {
                throw ValidationException::withMessages([
                    'stock_id' => 'Source stock record not found. Please refresh and try again.',
                ]);
            }

            if ((int) $lockedSource->branch_id === $toBranchId) {
                throw ValidationException::withMessages([
                    'to_branch_id' => 'Destination branch must be different from source branch.',
                ]);
            }

            if ((int) $lockedSource->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$lockedSource->quantity} unit(s) are available in source branch.",
                ]);
            }

            $batchNo = filled($toBatchNo) ? (string) $toBatchNo : (string) $lockedSource->batch_no;
            $expiryDate = $lockedSource->expiry_date?->toDateString() ?? now()->addYear()->toDateString();
            $buyPrice = (float) $lockedSource->buy_price;
            $sellPrice = (float) $lockedSource->sell_price;

            $lockedDestination = Stock::query()
                ->where('medicine_id', (int) $lockedSource->medicine_id)
                ->where('branch_id', $toBranchId)
                ->where('batch_no', $batchNo)
                ->whereDate('expiry_date', $expiryDate)
                ->lockForUpdate()
                ->first();

            $lockedSource->quantity -= $quantity;
            $lockedSource->save();

            if ($lockedDestination) {
                $lockedDestination->quantity += $quantity;

                if ((float) $lockedDestination->buy_price <= 0) {
                    $lockedDestination->buy_price = $buyPrice;
                }

                if ((float) $lockedDestination->sell_price <= 0) {
                    $lockedDestination->sell_price = $sellPrice;
                }

                $lockedDestination->save();
            } else {
                $lockedDestination = Stock::query()->create([
                    'medicine_id' => (int) $lockedSource->medicine_id,
                    'branch_id' => $toBranchId,
                    'batch_no' => $batchNo,
                    'expiry_date' => $expiryDate,
                    'quantity' => $quantity,
                    'buy_price' => $buyPrice,
                    'sell_price' => $sellPrice,
                ]);
            }

            $transfer = StockTransfer::query()->create([
                'medicine_id' => (int) $lockedSource->medicine_id,
                'stock_id' => (int) $lockedSource->id,
                'from_branch_id' => (int) $lockedSource->branch_id,
                'to_branch_id' => $toBranchId,
                'batch_no' => $batchNo,
                'expiry_date' => $expiryDate,
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'sell_price' => $sellPrice,
                'transferred_by' => $transferredBy,
                'transferred_at' => now(),
                'note' => filled($note) ? $note : null,
            ]);
        }, attempts: 3);

        /** @var StockTransfer $transfer */
        return $transfer;
    }

    public function validatePurchaseReversal(array $attributes): void
    {
        $branchId = $this->getPurchaseBranchId((int) $attributes['purchase_id']);
        $quantity = (int) $attributes['quantity'];

        $stock = $this->findPurchaseStock(
            medicineId: (int) $attributes['medicine_id'],
            branchId: $branchId,
            batchNo: (string) $attributes['batch_no'],
            expiryDate: (string) $attributes['expiry_date'],
            forUpdate: false,
        );

        if (! $stock || ($stock->quantity < $quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Cannot reduce this purchase item because some units are already consumed by sales.',
            ]);
        }
    }

    public function applyPurchaseItem(PurchaseItem $purchaseItem): void
    {
        DB::transaction(function () use ($purchaseItem): void {
            $branchId = $this->getPurchaseBranchId((int) $purchaseItem->purchase_id);
            $quantity = (int) $purchaseItem->quantity;
            $buyPrice = (float) $purchaseItem->buy_price;
            $expiryDate = $purchaseItem->expiry_date?->toDateString() ?? now()->addYear()->toDateString();

            $stock = $this->findPurchaseStock(
                medicineId: (int) $purchaseItem->medicine_id,
                branchId: $branchId,
                batchNo: (string) $purchaseItem->batch_no,
                expiryDate: $expiryDate,
                forUpdate: true,
            );

            if ($stock) {
                $stock->quantity += $quantity;
                $stock->buy_price = $buyPrice;

                if ((float) $stock->sell_price <= 0) {
                    $stock->sell_price = $this->guessSellPrice(
                        medicineId: (int) $purchaseItem->medicine_id,
                        branchId: $branchId,
                        fallbackBuyPrice: $buyPrice,
                    );
                }

                $stock->save();
            } else {
                Stock::query()->create([
                    'medicine_id' => $purchaseItem->medicine_id,
                    'branch_id' => $branchId,
                    'batch_no' => $purchaseItem->batch_no,
                    'expiry_date' => $expiryDate,
                    'quantity' => $quantity,
                    'buy_price' => $buyPrice,
                    'sell_price' => $this->guessSellPrice(
                        medicineId: (int) $purchaseItem->medicine_id,
                        branchId: $branchId,
                        fallbackBuyPrice: $buyPrice,
                    ),
                ]);
            }

            $this->createMovement(
                medicineId: (int) $purchaseItem->medicine_id,
                type: 'in',
                quantity: $quantity,
                reference: 'purchase',
            );

            $this->syncPurchaseTotal((int) $purchaseItem->purchase_id);
        }, attempts: 3);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function revertPurchaseItem(array $attributes): void
    {
        DB::transaction(function () use ($attributes): void {
            $branchId = $this->getPurchaseBranchId((int) $attributes['purchase_id']);
            $quantity = (int) $attributes['quantity'];

            $stock = $this->findPurchaseStock(
                medicineId: (int) $attributes['medicine_id'],
                branchId: $branchId,
                batchNo: (string) $attributes['batch_no'],
                expiryDate: (string) $attributes['expiry_date'],
                forUpdate: true,
            );

            if (! $stock || ($stock->quantity < $quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Unable to update this purchase item due to insufficient stock balance.',
                ]);
            }

            $stock->quantity -= $quantity;
            $stock->save();

            $this->createMovement(
                medicineId: (int) $attributes['medicine_id'],
                type: 'adjustment',
                quantity: $quantity,
                reference: 'purchase',
            );

            $this->syncPurchaseTotal((int) $attributes['purchase_id']);
        }, attempts: 3);
    }

    public function validateAndPrepareSaleItem(SaleItem $saleItem, int $availableBuffer = 0): void
    {
        $branchId = $this->getSaleBranchId((int) $saleItem->sale_id);
        $requestedQuantity = max(1, (int) $saleItem->quantity);

        $saleItem->quantity = $requestedQuantity;

        $stock = $this->findSaleStock(
            medicineId: (int) $saleItem->medicine_id,
            branchId: $branchId,
            batchNo: $saleItem->batch_no,
            forUpdate: false,
            onlyPositiveQuantity: $availableBuffer <= 0,
        );

        if (! $stock) {
            throw ValidationException::withMessages([
                'medicine_id' => 'No available stock for the selected medicine in this branch.',
            ]);
        }

        if (blank($saleItem->batch_no)) {
            $saleItem->batch_no = $stock->batch_no;
        }

        if (((float) $saleItem->price) <= 0) {
            $saleItem->price = (float) $stock->sell_price;
        }

        $availableQuantity = (int) $stock->quantity + max(0, $availableBuffer);
        if ($requestedQuantity > $availableQuantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$availableQuantity} unit(s) are available for batch {$stock->batch_no}.",
            ]);
        }
    }

    public function applySaleItem(SaleItem $saleItem): void
    {
        DB::transaction(function () use ($saleItem): void {
            $branchId = $this->getSaleBranchId((int) $saleItem->sale_id);
            $requestedQuantity = (int) $saleItem->quantity;

            $stock = $this->findSaleStock(
                medicineId: (int) $saleItem->medicine_id,
                branchId: $branchId,
                batchNo: (string) $saleItem->batch_no,
                forUpdate: true,
            );

            if (! $stock || ((int) $stock->quantity < $requestedQuantity)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock is not enough for this sale item.',
                ]);
            }

            $stock->quantity -= $requestedQuantity;
            $stock->save();

            $this->createMovement(
                medicineId: (int) $saleItem->medicine_id,
                type: 'out',
                quantity: $requestedQuantity,
                reference: 'sale',
            );

            $this->syncSaleTotals((int) $saleItem->sale_id);
        }, attempts: 3);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function revertSaleItem(array $attributes): void
    {
        DB::transaction(function () use ($attributes): void {
            $branchId = $this->getSaleBranchId((int) $attributes['sale_id']);
            $quantity = (int) $attributes['quantity'];

            $stock = Stock::query()
                ->where('medicine_id', (int) $attributes['medicine_id'])
                ->where('branch_id', $branchId)
                ->where('batch_no', (string) $attributes['batch_no'])
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = Stock::query()->create([
                    'medicine_id' => (int) $attributes['medicine_id'],
                    'branch_id' => $branchId,
                    'batch_no' => (string) $attributes['batch_no'],
                    'expiry_date' => now()->addYear()->toDateString(),
                    'quantity' => 0,
                    'buy_price' => (float) $attributes['price'],
                    'sell_price' => (float) $attributes['price'],
                ]);
            }

            $stock->quantity += $quantity;
            $stock->save();

            $this->createMovement(
                medicineId: (int) $attributes['medicine_id'],
                type: 'adjustment',
                quantity: $quantity,
                reference: 'sale',
            );

            $this->syncSaleTotals((int) $attributes['sale_id']);
        }, attempts: 3);
    }

    private function syncPurchaseTotal(int $purchaseId): void
    {
        $purchase = Purchase::query()->lockForUpdate()->find($purchaseId);

        if (! $purchase) {
            return;
        }

        $totalAmount = (float) PurchaseItem::query()
            ->where('purchase_id', $purchaseId)
            ->selectRaw('COALESCE(SUM(quantity * buy_price), 0) as total_amount')
            ->value('total_amount');

        $purchase->update([
            'total_amount' => round(max(0, $totalAmount), 2),
        ]);
    }

    private function syncSaleTotals(int $saleId): void
    {
        $sale = Sale::query()->lockForUpdate()->find($saleId);

        if (! $sale) {
            return;
        }

        $subtotal = (float) SaleItem::query()
            ->where('sale_id', $saleId)
            ->selectRaw('COALESCE(SUM(quantity * price), 0) as subtotal')
            ->value('subtotal');

        $total = round(max(0, $subtotal), 2);
        $netPayable = max(0, $total - (float) $sale->discount + (float) $sale->tax);
        $change = round(max(0, (float) $sale->paid_amount - $netPayable), 2);

        $sale->update([
            'total' => $total,
            'change' => $change,
        ]);
    }

    private function getPurchaseBranchId(int $purchaseId): int
    {
        $branchId = Purchase::query()->whereKey($purchaseId)->value('branch_id');

        if (! $branchId) {
            throw ValidationException::withMessages([
                'purchase_id' => 'Invalid purchase record. Please refresh and try again.',
            ]);
        }

        return (int) $branchId;
    }

    private function getSaleBranchId(int $saleId): int
    {
        $branchId = Sale::query()->whereKey($saleId)->value('branch_id');

        if (! $branchId) {
            throw ValidationException::withMessages([
                'sale_id' => 'Invalid sale record. Please refresh and try again.',
            ]);
        }

        return (int) $branchId;
    }

    private function findPurchaseStock(
        int $medicineId,
        int $branchId,
        string $batchNo,
        string $expiryDate,
        bool $forUpdate,
    ): ?Stock {
        $query = Stock::query()
            ->where('medicine_id', $medicineId)
            ->where('branch_id', $branchId)
            ->where('batch_no', $batchNo)
            ->whereDate('expiry_date', Carbon::parse($expiryDate)->toDateString());

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function findSaleStock(
        int $medicineId,
        int $branchId,
        ?string $batchNo,
        bool $forUpdate,
        bool $onlyPositiveQuantity = true,
    ): ?Stock {
        $query = Stock::query()
            ->where('medicine_id', $medicineId)
            ->where('branch_id', $branchId)
            ->whereDate('expiry_date', '>=', today());

        if ($onlyPositiveQuantity) {
            $query->where('quantity', '>', 0);
        }

        if (filled($batchNo)) {
            $query->where('batch_no', $batchNo);
        }

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->first();
    }

    private function guessSellPrice(int $medicineId, int $branchId, float $fallbackBuyPrice): float
    {
        $currentSellPrice = (float) Stock::query()
            ->where('medicine_id', $medicineId)
            ->where('branch_id', $branchId)
            ->orderByDesc('id')
            ->value('sell_price');

        if ($currentSellPrice > 0) {
            return $currentSellPrice;
        }

        return round(max(1, $fallbackBuyPrice * 1.2), 2);
    }

    private function createMovement(int $medicineId, string $type, int $quantity, ?string $reference): void
    {
        StockMovement::query()->create([
            'medicine_id' => $medicineId,
            'type' => $type,
            'quantity' => $quantity,
            'reference' => $reference,
        ]);
    }
}
