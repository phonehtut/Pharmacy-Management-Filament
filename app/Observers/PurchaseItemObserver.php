<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Services\Inventory\InventoryFlowService;

class PurchaseItemObserver
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $updatingSnapshots = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $deletingSnapshots = [];

    public function __construct(
        private readonly InventoryFlowService $inventoryFlowService,
    ) {}

    public function updating(PurchaseItem $purchaseItem): void
    {
        if (! $purchaseItem->isDirty($this->inventoryTrackedColumns())) {
            return;
        }

        $snapshot = $purchaseItem->getOriginal();
        $this->inventoryFlowService->validatePurchaseReversal($snapshot);
        $this->updatingSnapshots[(int) $purchaseItem->getKey()] = $snapshot;
    }

    /**
     * Handle the PurchaseItem "created" event.
     */
    public function created(PurchaseItem $purchaseItem): void
    {
        $this->inventoryFlowService->applyPurchaseItem($purchaseItem);
    }

    /**
     * Handle the PurchaseItem "updated" event.
     */
    public function updated(PurchaseItem $purchaseItem): void
    {
        if (! $purchaseItem->wasChanged($this->inventoryTrackedColumns())) {
            return;
        }

        $snapshot = $this->updatingSnapshots[(int) $purchaseItem->getKey()] ?? $purchaseItem->getOriginal();

        $this->inventoryFlowService->revertPurchaseItem($snapshot);
        $this->inventoryFlowService->applyPurchaseItem($purchaseItem);

        unset($this->updatingSnapshots[(int) $purchaseItem->getKey()]);
    }

    public function deleting(PurchaseItem $purchaseItem): void
    {
        $snapshot = $purchaseItem->getOriginal();
        $this->inventoryFlowService->validatePurchaseReversal($snapshot);
        $this->deletingSnapshots[(int) $purchaseItem->getKey()] = $snapshot;
    }

    /**
     * Handle the PurchaseItem "deleted" event.
     */
    public function deleted(PurchaseItem $purchaseItem): void
    {
        $snapshot = $this->deletingSnapshots[(int) $purchaseItem->getKey()] ?? $purchaseItem->getOriginal();
        $this->inventoryFlowService->revertPurchaseItem($snapshot);

        unset($this->deletingSnapshots[(int) $purchaseItem->getKey()]);
    }

    /**
     * @return array<int, string>
     */
    private function inventoryTrackedColumns(): array
    {
        return ['purchase_id', 'medicine_id', 'quantity', 'buy_price', 'expiry_date', 'batch_no'];
    }
}
