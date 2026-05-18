<x-filament-panels::page full-height>
    @php
        $catalog = $this->getProductCatalog();
        $cartItems = $this->getCartItemsForDisplay();
        $recentSales = $this->getRecentSalesHistory();
        $quickPayAmounts = [5000, 10000, 20000];
    @endphp

    @once
        @push('styles')
            <style>
                .pos-ui {
                    --pos-bg: transparent;
                    --pos-panel: transparent;
                    --pos-card: var(--color-white);
                    --pos-soft-card: var(--gray-50);
                    --pos-subtle-card: var(--gray-100);
                    --pos-border: color-mix(in oklab, var(--gray-950) 12%, transparent);
                    --pos-text: var(--gray-950);
                    --pos-muted: var(--gray-500);
                    --pos-primary: var(--primary-600);
                    --pos-success: var(--success-600);
                    --pos-danger: var(--danger-600);
                    --pos-shell-bg: rgba(245, 248, 252, 0.95);
                    --pos-shell-border: rgba(255, 255, 255, 0.36);
                    --pos-toolbar-bg: var(--color-white);
                    --pos-toolbar-border: color-mix(in oklab, var(--gray-950) 12%, transparent);
                    --pos-toolbar-text: var(--gray-700);
                    --pos-toolbar-hover-bg: var(--gray-50);
                    --pos-toolbar-hover-border: color-mix(in oklab, var(--gray-950) 20%, transparent);
                    --pos-chip-active-bg: color-mix(in oklab, var(--primary-500) 10%, var(--color-white));
                    --pos-chip-active-text: var(--primary-700);
                    --pos-chip-active-border: color-mix(in oklab, var(--primary-600) 35%, transparent);
                    --pos-danger-soft-bg: color-mix(in oklab, var(--danger-500) 10%, var(--color-white));
                    --pos-danger-soft-border: color-mix(in oklab, var(--danger-600) 30%, transparent);
                    --pos-success-soft-bg: color-mix(in oklab, var(--success-500) 10%, var(--color-white));
                    --pos-success-soft-border: color-mix(in oklab, var(--success-600) 30%, transparent);
                    --pos-success-soft-text: var(--success-700);
                    --pos-dashed-border: color-mix(in oklab, var(--gray-950) 20%, transparent);
                    --pos-shadow-soft: 0 10px 16px rgba(15, 23, 42, 0.08);
                    --pos-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
                    border-radius: 0;
                    background: var(--pos-bg);
                    padding: 0;
                    box-shadow: none;
                    animation: pos-fade-in 340ms ease-out;
                    min-height: auto;
                    color-scheme: light;
                }

                .dark .pos-ui,
                [data-theme="dark"] .pos-ui {
                    --pos-bg: transparent;
                    --pos-panel: transparent;
                    --pos-card: var(--gray-900);
                    --pos-soft-card: color-mix(in oklab, var(--color-white) 5%, transparent);
                    --pos-subtle-card: color-mix(in oklab, var(--color-white) 7%, transparent);
                    --pos-border: color-mix(in oklab, var(--color-white) 12%, transparent);
                    --pos-text: var(--color-white);
                    --pos-muted: var(--gray-400);
                    --pos-primary: var(--primary-500);
                    --pos-success: var(--success-500);
                    --pos-danger: var(--danger-500);
                    --pos-shell-bg: rgba(15, 23, 42, 0.86);
                    --pos-shell-border: rgba(148, 163, 184, 0.25);
                    --pos-toolbar-bg: var(--gray-900);
                    --pos-toolbar-border: color-mix(in oklab, var(--color-white) 12%, transparent);
                    --pos-toolbar-text: var(--gray-200);
                    --pos-toolbar-hover-bg: color-mix(in oklab, var(--color-white) 8%, transparent);
                    --pos-toolbar-hover-border: color-mix(in oklab, var(--color-white) 22%, transparent);
                    --pos-chip-active-bg: color-mix(in oklab, var(--primary-500) 22%, transparent);
                    --pos-chip-active-text: var(--primary-300);
                    --pos-chip-active-border: color-mix(in oklab, var(--primary-400) 55%, transparent);
                    --pos-danger-soft-bg: color-mix(in oklab, var(--danger-500) 18%, transparent);
                    --pos-danger-soft-border: color-mix(in oklab, var(--danger-400) 50%, transparent);
                    --pos-success-soft-bg: color-mix(in oklab, var(--success-500) 18%, transparent);
                    --pos-success-soft-border: color-mix(in oklab, var(--success-400) 50%, transparent);
                    --pos-success-soft-text: var(--success-300);
                    --pos-dashed-border: color-mix(in oklab, var(--color-white) 18%, transparent);
                    --pos-shadow-soft: 0 10px 20px rgba(2, 6, 23, 0.6);
                    --pos-shadow: 0 18px 40px rgba(2, 6, 23, 0.62);
                    color-scheme: dark;
                }

                .pos-shell {
                    border-radius: 0;
                    border: 0;
                    background: transparent;
                    padding: 0;
                    backdrop-filter: none;
                }

                .pos-toolbar {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.42rem;
                }

                .pos-toolbar.pos-toolbar-standalone {
                    justify-content: end;
                    margin-bottom: 0.78rem;
                }

                .pos-toolbar a,
                .pos-toolbar button {
                    border-radius: 10px;
                    border: 1px solid var(--pos-toolbar-border);
                    background: var(--pos-toolbar-bg);
                    color: var(--pos-toolbar-text);
                    font-size: 0.72rem;
                    font-weight: 700;
                    padding: 0.4rem 0.62rem;
                    text-decoration: none;
                    transition: 170ms ease;
                }

                .pos-toolbar button {
                    cursor: pointer;
                }

                .pos-toolbar a:hover,
                .pos-toolbar button:hover {
                    border-color: var(--pos-toolbar-hover-border);
                    background: var(--pos-toolbar-hover-bg);
                }

                .pos-grid {
                    display: grid;
                    gap: 0.85rem;
                    grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.95fr);
                    align-items: start;
                }

                .pos-main {
                    border-radius: 0;
                    border: 0;
                    background: var(--pos-panel);
                    padding: 0;
                }

                .pos-order {
                    position: sticky;
                    top: 1.2rem;
                    border-radius: 0;
                    border: 0;
                    background: transparent;
                    padding: 0;
                    box-shadow: none;
                }

                .pos-heading {
                    font-size: 0.96rem;
                    font-weight: 800;
                    color: var(--pos-text);
                    margin-bottom: 0.6rem;
                }

                .pos-field-grid {
                    display: grid;
                    gap: 0.68rem;
                    grid-template-columns: minmax(0, 2fr) minmax(210px, 1fr);
                }

                .pos-label {
                    color: var(--pos-toolbar-text);
                    font-size: 0.77rem;
                    font-weight: 700;
                    margin-bottom: 0.3rem;
                    display: block;
                }

                .pos-kpis {
                    margin-top: 0.68rem;
                    display: grid;
                    gap: 0.52rem;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .pos-kpi {
                    border-radius: 10px;
                    border: 1px solid var(--pos-border);
                    background: var(--pos-card);
                    padding: 0.5rem;
                }

                .pos-kpi-title {
                    color: var(--pos-muted);
                    font-size: 0.67rem;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                    margin-bottom: 0.22rem;
                    font-weight: 700;
                }

                .pos-kpi-value {
                    color: var(--pos-text);
                    font-size: 1rem;
                    font-weight: 800;
                    line-height: 1.15;
                }

                .pos-kpi-value.is-success {
                    color: var(--pos-success);
                }

                .pos-categories {
                    margin-top: 0.68rem;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.44rem;
                }

                .pos-chip {
                    border: 1px solid var(--pos-border);
                    background: var(--pos-card);
                    color: var(--pos-toolbar-text);
                    padding: 0.32rem 0.65rem;
                    border-radius: 999px;
                    font-size: 0.71rem;
                    font-weight: 700;
                    cursor: pointer;
                    transition: 170ms ease;
                }

                .pos-chip:hover {
                    border-color: var(--pos-toolbar-hover-border);
                    background: var(--pos-toolbar-hover-bg);
                }

                .pos-chip.is-active {
                    border-color: var(--pos-chip-active-border);
                    background: var(--pos-chip-active-bg);
                    color: var(--pos-chip-active-text);
                }

                .pos-product-grid {
                    margin-top: 0.72rem;
                    display: grid;
                    gap: 0.58rem;
                    grid-template-columns: repeat(5, minmax(0, 1fr));
                }

                .pos-product {
                    border-radius: 10px;
                    border: 1px solid var(--pos-border);
                    background: var(--pos-card);
                    padding: 0.6rem 0.55rem;
                    text-align: center;
                    cursor: pointer;
                    transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
                }

                .pos-product:hover {
                    transform: translateY(-1px);
                    border-color: var(--pos-toolbar-hover-border);
                    box-shadow: var(--pos-shadow-soft);
                }

                .pos-product-name {
                    color: var(--pos-text);
                    font-size: 0.8rem;
                    font-weight: 800;
                    line-height: 1.3;
                    margin-bottom: 0.18rem;
                }

                .pos-product-price {
                    color: var(--pos-primary);
                    font-size: 0.95rem;
                    font-weight: 800;
                    line-height: 1.18;
                    margin-bottom: 0.12rem;
                }

                .pos-product-stock,
                .pos-product-meta {
                    color: var(--pos-muted);
                    font-size: 0.68rem;
                    line-height: 1.2;
                }

                .pos-order-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 0.6rem;
                }

                .pos-order-title {
                    color: var(--pos-text);
                    font-size: 1.03rem;
                    font-weight: 800;
                }

                .pos-small {
                    color: var(--pos-muted);
                    font-size: 0.7rem;
                    font-weight: 600;
                }

                .pos-error-text {
                    color: var(--pos-danger);
                    margin-top: 0.25rem;
                }

                .pos-scanner {
                    border: 1px solid var(--pos-border);
                    border-radius: 10px;
                    background: var(--pos-card);
                    padding: 0.56rem;
                    margin-top: 0.52rem;
                }

                .pos-scanner video {
                    margin-top: 0.45rem;
                    width: 100%;
                    border-radius: 8px;
                    border: 1px solid var(--pos-border);
                }

                .pos-cart {
                    margin-top: 0.62rem;
                    border-radius: 10px;
                    border: 1px solid var(--pos-border);
                    background: var(--pos-card);
                    padding: 0.54rem;
                    max-height: 18.5rem;
                    overflow: auto;
                }

                .pos-cart-row {
                    border: 1px solid var(--pos-border);
                    border-radius: 9px;
                    background: var(--pos-subtle-card);
                    padding: 0.5rem;
                }

                .pos-cart-row + .pos-cart-row {
                    margin-top: 0.48rem;
                }

                .pos-cart-line1 {
                    display: flex;
                    align-items: start;
                    justify-content: space-between;
                    gap: 0.45rem;
                }

                .pos-cart-name {
                    color: var(--pos-text);
                    font-size: 0.78rem;
                    font-weight: 800;
                    line-height: 1.28;
                }

                .pos-cart-price {
                    color: var(--pos-muted);
                    font-size: 0.68rem;
                    margin-top: 0.12rem;
                }

                .pos-cart-total {
                    font-size: 0.8rem;
                    font-weight: 800;
                    color: var(--pos-text);
                    white-space: nowrap;
                }

                .pos-cart-actions {
                    margin-top: 0.42rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 0.4rem;
                }

                .pos-qty {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                }

                .pos-qty button {
                    width: 1.4rem;
                    height: 1.4rem;
                    border: 1px solid var(--pos-border);
                    border-radius: 6px;
                    background: var(--pos-card);
                    color: var(--pos-toolbar-text);
                    font-weight: 800;
                    cursor: pointer;
                    line-height: 1;
                }

                .pos-qty span {
                    min-width: 1.45rem;
                    text-align: center;
                    font-size: 0.78rem;
                    font-weight: 800;
                    color: var(--pos-toolbar-text);
                }

                .pos-remove {
                    border: 1px solid var(--pos-danger-soft-border);
                    background: var(--pos-danger-soft-bg);
                    color: var(--pos-danger);
                    border-radius: 7px;
                    padding: 0.2rem 0.43rem;
                    font-size: 0.67rem;
                    font-weight: 800;
                    cursor: pointer;
                }

                .pos-summary {
                    margin-top: 0.62rem;
                    border-radius: 10px;
                    border: 1px solid var(--pos-border);
                    background: var(--pos-card);
                    padding: 0.6rem;
                }

                .pos-summary-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    font-size: 0.75rem;
                    color: var(--pos-muted);
                    margin-bottom: 0.34rem;
                }

                .pos-summary-row strong {
                    color: var(--pos-text);
                    font-size: 0.8rem;
                }

                .pos-total-row {
                    border-top: 1px dashed var(--pos-dashed-border);
                    padding-top: 0.42rem;
                    margin-top: 0.2rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .pos-total-row span {
                    color: var(--pos-text);
                    font-weight: 800;
                    font-size: 0.9rem;
                }

                .pos-total-row strong {
                    color: var(--pos-text);
                    font-size: 1.22rem;
                    font-weight: 900;
                    line-height: 1.1;
                }

                .pos-outstanding {
                    color: var(--pos-success);
                }

                .pos-outstanding.is-due {
                    color: var(--danger-500);
                }

                .pos-pay-grid {
                    margin-top: 0.56rem;
                    display: grid;
                    gap: 0.5rem;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .pos-quick-pay {
                    margin-top: 0.54rem;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.38rem;
                }

                .pos-quick-pay button {
                    border: 1px solid var(--pos-border);
                    border-radius: 8px;
                    background: var(--pos-card);
                    color: var(--pos-toolbar-text);
                    font-size: 0.69rem;
                    font-weight: 800;
                    padding: 0.3rem 0.52rem;
                    cursor: pointer;
                }

                .pos-quick-pay button:hover {
                    border-color: var(--pos-toolbar-hover-border);
                    background: var(--pos-toolbar-hover-bg);
                }

                .pos-quick-pay button.pos-pay-exact {
                    border-color: var(--pos-success-soft-border);
                    background: var(--pos-success-soft-bg);
                    color: var(--pos-success-soft-text);
                }

                .pos-checkout {
                    margin-top: 0.72rem;
                }

                .pos-disabled-note {
                    margin-top: 0.38rem;
                    text-align: center;
                    color: var(--pos-muted);
                    font-size: 0.69rem;
                    font-weight: 600;
                }

                .pos-recent {
                    margin-top: 0.74rem;
                    border-radius: 10px;
                    border: 1px solid var(--pos-border);
                    background: var(--pos-card);
                    padding: 0.58rem;
                }

                .pos-recent-row {
                    display: grid;
                    grid-template-columns: auto 1fr auto;
                    gap: 0.5rem;
                    align-items: center;
                    border-radius: 8px;
                    border: 1px solid var(--pos-border);
                    background: var(--pos-subtle-card);
                    padding: 0.44rem 0.48rem;
                }

                .pos-recent-row + .pos-recent-row {
                    margin-top: 0.4rem;
                }

                .pos-recent-id {
                    color: var(--pos-muted);
                    font-size: 0.67rem;
                    font-weight: 800;
                }

                .pos-recent-name {
                    color: var(--pos-text);
                    font-size: 0.74rem;
                    font-weight: 800;
                    margin-bottom: 0.08rem;
                }

                .pos-recent-meta {
                    color: var(--pos-muted);
                    font-size: 0.66rem;
                }

                .pos-recent-total {
                    color: var(--pos-text);
                    font-size: 0.74rem;
                    font-weight: 800;
                }

                .pos-empty {
                    border: 1px dashed var(--pos-dashed-border);
                    border-radius: 8px;
                    padding: 0.88rem;
                    text-align: center;
                    color: var(--pos-muted);
                    font-size: 0.73rem;
                    font-weight: 600;
                }

                @keyframes pos-fade-in {
                    from {
                        opacity: 0;
                        transform: translateY(4px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @media (max-width: 1460px) {
                    .pos-product-grid {
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                    }
                }

                @media (max-width: 1280px) {
                    .pos-grid {
                        grid-template-columns: 1fr;
                    }
                    .pos-order {
                        position: static;
                    }
                }

                @media (max-width: 1024px) {
                    .pos-field-grid {
                        grid-template-columns: 1fr;
                    }
                    .pos-product-grid {
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                    }
                    .pos-kpis {
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                    }
                }

                @media (max-width: 700px) {
                    .pos-ui {
                        padding: 0.72rem;
                    }
                    .pos-shell {
                        padding: 0.72rem;
                    }
                    .pos-toolbar.pos-toolbar-standalone {
                        justify-content: flex-start;
                    }
                    .pos-product-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                    .pos-kpis {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                    .pos-pay-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        @endpush
    @endonce

    <div class="pos-ui">
        <div class="pos-toolbar pos-toolbar-standalone">
            <a href="{{ \App\Filament\Cashier\Pages\SaleHistory::getUrl() }}">Sale History</a>
            <button type="button" wire:click="clearCart">Clear Cart</button>
        </div>

        <div class="pos-shell">
            <div class="pos-grid">
                <section class="pos-main">
                    <div class="pos-heading">Product Catalog</div>

                    <div class="pos-field-grid">
                        <div>
                            <label class="pos-label">Search Products</label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="text"
                                    wire:model.live.debounce.250ms="productSearch"
                                    placeholder="Search by medicine, generic name, barcode..."
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="pos-label">Customer</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="customer_id">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($this->getCustomerOptions() as $customerId => $customerName)
                                        <option value="{{ $customerId }}">{{ $customerName }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    <div class="pos-kpis">
                        <article class="pos-kpi">
                            <p class="pos-kpi-title">Lines</p>
                            <p class="pos-kpi-value">{{ number_format($this->cartLines) }}</p>
                        </article>
                        <article class="pos-kpi">
                            <p class="pos-kpi-title">Items</p>
                            <p class="pos-kpi-value">{{ number_format($this->cartQuantity) }}</p>
                        </article>
                        <article class="pos-kpi">
                            <p class="pos-kpi-title">Change</p>
                            <p class="pos-kpi-value is-success">{{ number_format($this->change, 2) }}</p>
                        </article>
                    </div>

                    <div class="pos-categories">
                        <button
                            type="button"
                            class="pos-chip {{ $selectedCategory === 'all' ? 'is-active' : '' }}"
                            wire:click="$set('selectedCategory', 'all')"
                        >
                            All
                        </button>

                        @foreach($this->getCategoryOptions() as $categoryName)
                            <button
                                type="button"
                                class="pos-chip {{ $selectedCategory === $categoryName ? 'is-active' : '' }}"
                                wire:click="selectCategory(@js($categoryName))"
                            >
                                {{ $categoryName }}
                            </button>
                        @endforeach
                    </div>

                    <div class="pos-product-grid">
                        @forelse($catalog as $product)
                            <button
                                type="button"
                                class="pos-product"
                                wire:click="addProductToCart({{ $product['medicine_id'] }})"
                            >
                                <p class="pos-product-name">{{ $product['name'] }}</p>
                                <p class="pos-product-price">{{ number_format($product['sell_price'], 2) }}</p>
                                <p class="pos-product-stock">Stock: {{ number_format($product['stock']) }}</p>
                                <p class="pos-product-meta">{{ $product['barcode'] ?: '-' }}</p>
                            </button>
                        @empty
                            <div class="pos-empty">No products found for this category/filter.</div>
                        @endforelse
                    </div>

                    <div class="pos-recent">
                        <div class="pos-order-head">
                            <p class="pos-heading" style="margin-bottom:0;">Recent Sales</p>
                            <a href="{{ \App\Filament\Cashier\Pages\SaleHistory::getUrl() }}" class="pos-small">View all</a>
                        </div>

                        @forelse($recentSales as $sale)
                            <article class="pos-recent-row">
                                <p class="pos-recent-id">#{{ $sale['id'] }}</p>
                                <div>
                                    <p class="pos-recent-name">{{ $sale['customer'] }}</p>
                                    <p class="pos-recent-meta">{{ $sale['items_count'] }} items · {{ $sale['sold_at'] }}</p>
                                </div>
                                <p class="pos-recent-total">{{ number_format($sale['total'], 2) }}</p>
                            </article>
                        @empty
                            <div class="pos-empty">No sales yet for this branch.</div>
                        @endforelse
                    </div>
                </section>

                <aside class="pos-order">
                    <form wire:submit="checkout">
                        <div class="pos-order-head">
                            <h3 class="pos-order-title">Current Order</h3>
                            <p class="pos-small">{{ number_format($this->cartLines) }} lines</p>
                        </div>

                        <label class="pos-label">Barcode Input</label>
                        <div style="display:flex;gap:.45rem;">
                            <x-filament::input.wrapper style="flex:1;">
                                <x-filament::input
                                    id="barcode-input"
                                    type="text"
                                    wire:model="barcodeInput"
                                    wire:keydown.enter.prevent="addByBarcode($event.target.value)"
                                    placeholder="Scan or type barcode"
                                />
                            </x-filament::input.wrapper>
                            <x-filament::button type="button" color="gray" wire:click="addByBarcode">Add</x-filament::button>
                        </div>
                        @error('barcodeInput')
                            <p class="pos-small pos-error-text">{{ $message }}</p>
                        @enderror

                        <div class="pos-scanner">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:.35rem;">
                                <p class="pos-small">Camera Scanner</p>
                                <button id="pos-scanner-toggle" type="button" class="pos-chip" onclick="window.togglePosScanner()">Start Camera</button>
                            </div>
                            <video id="pos-scanner-video" class="hidden" autoplay muted playsinline></video>
                            <p id="pos-scanner-status" class="pos-small" style="margin-top:.35rem;">Scanner idle.</p>
                        </div>

                        <div class="pos-cart">
                            @forelse($cartItems as $cartItem)
                                <article class="pos-cart-row">
                                    <div class="pos-cart-line1">
                                        <div>
                                            <p class="pos-cart-name">{{ $cartItem['medicine_name'] }}</p>
                                            <p class="pos-cart-price">{{ number_format($cartItem['price'], 2) }} MMK each</p>
                                        </div>
                                        <p class="pos-cart-total">{{ number_format($cartItem['line_total'], 2) }}</p>
                                    </div>

                                    <div class="pos-cart-actions">
                                        <div class="pos-qty">
                                            <button type="button" wire:click="decrementQuantity({{ $cartItem['index'] }})">-</button>
                                            <span>{{ $cartItem['quantity'] }}</span>
                                            <button type="button" wire:click="incrementQuantity({{ $cartItem['index'] }})">+</button>
                                        </div>
                                        <button type="button" class="pos-remove" wire:click="removeItem({{ $cartItem['index'] }})">Remove</button>
                                    </div>
                                </article>
                            @empty
                                <div class="pos-empty">Cart is empty. Add products from the left side.</div>
                            @endforelse
                        </div>

                        <div class="pos-pay-grid">
                            <div>
                                <label class="pos-label">Discount</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" min="0" step="0.01" wire:model.live="discount" />
                                </x-filament::input.wrapper>
                            </div>
                            <div>
                                <label class="pos-label">Tax</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" min="0" step="0.01" wire:model.live="tax" />
                                </x-filament::input.wrapper>
                            </div>
                            <div>
                                <label class="pos-label">Paid</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" min="0" step="0.01" wire:model.live="paid_amount" />
                                </x-filament::input.wrapper>
                            </div>
                        </div>

                        <div class="pos-quick-pay">
                            @foreach($quickPayAmounts as $amount)
                                <button type="button" wire:click="addPaidAmount({{ $amount }})">+{{ number_format($amount) }}</button>
                            @endforeach
                            <button type="button" class="pos-pay-exact" wire:click="setPaidToNetPayable">Pay Exact</button>
                        </div>
                        @error('paid_amount')
                            <p class="pos-small pos-error-text">{{ $message }}</p>
                        @enderror
                        @error('items')
                            <p class="pos-small pos-error-text">{{ $message }}</p>
                        @enderror

                        <div class="pos-summary">
                            <div class="pos-summary-row">
                                <span>Subtotal</span>
                                <strong>{{ number_format($this->subtotal, 2) }} MMK</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span>Discount</span>
                                <strong>-{{ number_format($this->discount, 2) }} MMK</strong>
                            </div>
                            <div class="pos-summary-row">
                                <span>Tax</span>
                                <strong>+{{ number_format($this->tax, 2) }} MMK</strong>
                            </div>
                            <div class="pos-total-row">
                                <span>Total</span>
                                <strong>{{ number_format($this->netPayable, 2) }}</strong>
                            </div>
                            <div class="pos-summary-row" style="margin-bottom:0;margin-top:.45rem;">
                                <span>Outstanding</span>
                                <strong class="pos-outstanding {{ $this->outstanding > 0 ? 'is-due' : '' }}">
                                    {{ number_format($this->outstanding, 2) }} MMK
                                </strong>
                            </div>
                        </div>

                        <div class="pos-checkout">
                            <x-filament::button
                                type="submit"
                                class="w-full"
                                color="success"
                                :disabled="! $this->canCheckout || $this->isCheckingOut"
                                wire:loading.attr="disabled"
                                wire:target="checkout"
                            >
                                <span wire:loading.remove wire:target="checkout">Checkout</span>
                                <span wire:loading wire:target="checkout">Processing...</span>
                            </x-filament::button>
                        </div>

                        @if (! $this->canCheckout)
                            <p class="pos-disabled-note">Add product and make sure paid amount covers total.</p>
                        @endif
                    </form>
                </aside>
            </div>
        </div>
    </div>

    @script
        <script>
            let scannerStream = null;
            let scannerInterval = null;
            let scannerActive = false;
            let scannerResultLocked = false;
            let scannerDetectionInProgress = false;
            let zxingReader = null;
            let zxingLoaderPromise = null;
            let lastPrintUrl = null;
            let lastPrintAt = 0;
            const scannerCameraConstraints = {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1280 },
                height: { ideal: 720 },
            };

            const scannerElements = () => ({
                status: document.getElementById('pos-scanner-status'),
                video: document.getElementById('pos-scanner-video'),
                toggle: document.getElementById('pos-scanner-toggle'),
                barcodeInput: document.getElementById('barcode-input'),
            });

            const setScannerStatus = (message) => {
                const { status } = scannerElements();
                if (status) {
                    status.textContent = message;
                }
            };

            const setScannerButton = () => {
                const { toggle } = scannerElements();
                if (toggle) {
                    toggle.textContent = scannerActive ? 'Stop Camera' : 'Start Camera';
                }
            };

            const stopScanner = () => {
                if (scannerInterval) {
                    clearInterval(scannerInterval);
                    scannerInterval = null;
                }

                if (zxingReader) {
                    try {
                        zxingReader.reset();
                    } catch (error) {
                        // noop
                    }
                    zxingReader = null;
                }

                if (scannerStream) {
                    scannerStream.getTracks().forEach((track) => track.stop());
                    scannerStream = null;
                }

                const { video } = scannerElements();
                const activeVideoStream = video?.srcObject;
                if (activeVideoStream && typeof activeVideoStream.getTracks === 'function') {
                    activeVideoStream.getTracks().forEach((track) => track.stop());
                }

                if (video) {
                    video.classList.add('hidden');
                    video.srcObject = null;
                }

                scannerActive = false;
                scannerResultLocked = false;
                scannerDetectionInProgress = false;
                setScannerButton();
            };

            const openVoucherPrint = (printUrl) => {
                if (!printUrl) {
                    return;
                }

                const now = Date.now();
                if (lastPrintUrl === printUrl && (now - lastPrintAt) < 5000) {
                    return;
                }
                lastPrintUrl = printUrl;
                lastPrintAt = now;

                const printWindow = window.open(
                    printUrl,
                    '_blank',
                    'noopener,noreferrer,width=520,height=860',
                );

                if (!printWindow) {
                    alert('Popup was blocked. Please allow popups for this site to print voucher.');
                }
            };

            const startScanner = async () => {
                try {
                    stopScanner();

                    const { video } = scannerElements();
                    if (!video) {
                        return;
                    }

                    if ('BarcodeDetector' in window) {
                        const detector = new BarcodeDetector({
                            formats: ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39'],
                        });

                        scannerStream = await navigator.mediaDevices.getUserMedia({
                            video: scannerCameraConstraints,
                            audio: false,
                        });

                        video.srcObject = scannerStream;
                        video.classList.remove('hidden');
                        scannerActive = true;
                        setScannerButton();
                        setScannerStatus('Scanner started. Point camera to barcode.');

                        scannerInterval = setInterval(async () => {
                            try {
                                if (scannerResultLocked || scannerDetectionInProgress) {
                                    return;
                                }

                                if (video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
                                    return;
                                }

                                scannerDetectionInProgress = true;
                                const barcodes = await detector.detect(video);
                                const detectedValue = barcodes[0]?.rawValue;

                                if (!detectedValue) {
                                    return;
                                }

                                scannerResultLocked = true;
                                setScannerStatus(`Detected: ${detectedValue}`);
                                await $wire.$call('addByBarcode', detectedValue);
                                stopScanner();
                                setScannerStatus(`Added barcode ${detectedValue}`);

                                const { barcodeInput } = scannerElements();
                                barcodeInput?.focus();
                            } catch (error) {
                                setScannerStatus('Scanning...');
                            } finally {
                                scannerDetectionInProgress = false;
                            }
                        }, 200);

                        return;
                    }

                    if (!zxingLoaderPromise) {
                        zxingLoaderPromise = new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js';
                            script.async = true;
                            script.onload = () => {
                                if (window.ZXing?.BrowserMultiFormatReader) {
                                    resolve(window.ZXing);
                                    return;
                                }

                                reject(new Error('Fallback scanner library failed to initialize.'));
                            };
                            script.onerror = () => reject(new Error('Unable to load fallback scanner library.'));
                            document.head.appendChild(script);
                        });
                    }

                    setScannerStatus('Loading fallback scanner...');
                    const ZXing = await zxingLoaderPromise;

                    zxingReader = new ZXing.BrowserMultiFormatReader();
                    video.classList.remove('hidden');
                    scannerActive = true;
                    setScannerButton();
                    setScannerStatus('Scanner started (fallback mode). Point camera to barcode.');

                    await zxingReader.decodeFromConstraints({
                        video: scannerCameraConstraints,
                    }, video, async (result, error) => {
                        if (scannerResultLocked) {
                            return;
                        }

                        const detectedValue = result?.getText?.() ?? null;

                        if (detectedValue) {
                            scannerResultLocked = true;
                            setScannerStatus(`Detected: ${detectedValue}`);
                            await $wire.$call('addByBarcode', detectedValue);
                            stopScanner();
                            setScannerStatus(`Added barcode ${detectedValue}`);

                            const { barcodeInput } = scannerElements();
                            barcodeInput?.focus();

                            return;
                        }

                        if (!error) {
                            return;
                        }

                        const ignoredErrors = ['NotFoundException', 'ChecksumException', 'FormatException'];
                        if (!ignoredErrors.includes(error?.name ?? '')) {
                            setScannerStatus('Scanning...');
                        }
                    });
                } catch (error) {
                    stopScanner();
                    if ((error?.message ?? '').includes('fallback scanner')) {
                        zxingLoaderPromise = null;
                    }
                    setScannerStatus(error?.message ?? 'Unable to access camera. Use manual barcode input.');
                }
            };

            window.togglePosScanner = async () => {
                if (scannerActive) {
                    stopScanner();
                    setScannerStatus('Scanner stopped.');
                    return;
                }

                await startScanner();
            };

            const handlePosSaleCompleted = (event) => {
                const printUrl = event?.detail?.printUrl ?? event?.printUrl ?? null;
                openVoucherPrint(printUrl);
            };

            if (window.__posSaleCompletedHandler) {
                window.removeEventListener('pos-sale-completed', window.__posSaleCompletedHandler);
            }
            window.__posSaleCompletedHandler = handlePosSaleCompleted;

            document.addEventListener('livewire:navigating', stopScanner);
            window.addEventListener('pagehide', stopScanner);
            window.addEventListener('pos-sale-completed', window.__posSaleCompletedHandler);
            setScannerButton();
        </script>
    @endscript
</x-filament-panels::page>
