<?php

namespace App\Observers;

use App\Models\SaleItem;
use App\Services\Inventory\InventoryFlowService;

class SaleItemObserver
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

    public function creating(SaleItem $saleItem): void
    {
        $this->inventoryFlowService->validateAndPrepareSaleItem($saleItem);
    }

    public function updating(SaleItem $saleItem): void
    {
        if (! $saleItem->isDirty($this->inventoryTrackedColumns())) {
            return;
        }

        $snapshot = $saleItem->getOriginal();
        $buffer = $this->isSameStockPath($saleItem, $snapshot)
            ? (int) ($snapshot['quantity'] ?? 0)
            : 0;

        $this->inventoryFlowService->validateAndPrepareSaleItem($saleItem, availableBuffer: $buffer);
        $this->updatingSnapshots[(int) $saleItem->getKey()] = $snapshot;
    }

    /**
     * Handle the SaleItem "created" event.
     */
    public function created(SaleItem $saleItem): void
    {
        $this->inventoryFlowService->applySaleItem($saleItem);
    }

    /**
     * Handle the SaleItem "updated" event.
     */
    public function updated(SaleItem $saleItem): void
    {
        if (! $saleItem->wasChanged($this->inventoryTrackedColumns())) {
            return;
        }

        $snapshot = $this->updatingSnapshots[(int) $saleItem->getKey()] ?? $saleItem->getOriginal();

        $this->inventoryFlowService->revertSaleItem($snapshot);
        $this->inventoryFlowService->applySaleItem($saleItem);

        unset($this->updatingSnapshots[(int) $saleItem->getKey()]);
    }

    public function deleting(SaleItem $saleItem): void
    {
        $this->deletingSnapshots[(int) $saleItem->getKey()] = $saleItem->getOriginal();
    }

    /**
     * Handle the SaleItem "deleted" event.
     */
    public function deleted(SaleItem $saleItem): void
    {
        $snapshot = $this->deletingSnapshots[(int) $saleItem->getKey()] ?? $saleItem->getOriginal();
        $this->inventoryFlowService->revertSaleItem($snapshot);

        unset($this->deletingSnapshots[(int) $saleItem->getKey()]);
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function isSameStockPath(SaleItem $saleItem, array $snapshot): bool
    {
        return ((int) $saleItem->sale_id === (int) ($snapshot['sale_id'] ?? 0))
            && ((int) $saleItem->medicine_id === (int) ($snapshot['medicine_id'] ?? 0))
            && ((string) $saleItem->batch_no === (string) ($snapshot['batch_no'] ?? ''));
    }

    /**
     * @return array<int, string>
     */
    private function inventoryTrackedColumns(): array
    {
        return ['sale_id', 'medicine_id', 'quantity', 'price', 'batch_no'];
    }
}
