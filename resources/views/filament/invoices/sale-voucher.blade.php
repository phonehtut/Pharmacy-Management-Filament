<div style="font-family: 'Courier New', monospace; color: #111827; background: #ffffff; padding: 16px; font-size: 12px; line-height: 1.4; max-width: 360px; margin: 0 auto;">
    <div style="text-align: center; border-bottom: 1px dashed #9ca3af; padding-bottom: 10px; margin-bottom: 10px;">
        <div style="font-size: 16px; font-weight: 700;">{{ $sale->branch?->pharmacy?->name ?? 'G&G Pharmacy' }}</div>
        <div>{{ $sale->branch?->name ?? 'Main Branch' }}</div>
        <div>{{ $sale->branch?->location ?? '' }}</div>
    </div>

    <div style="margin-bottom: 8px;">
        <div><strong>Voucher:</strong> #{{ $sale->id }}</div>
        <div><strong>Date:</strong> {{ $sale->sold_at?->format('Y-m-d H:i') ?? '-' }}</div>
        <div><strong>Cashier:</strong> {{ $sale->user?->name ?? '-' }}</div>
        <div><strong>Customer:</strong> {{ $sale->customer?->name ?? 'Walk-in' }}</div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
        <thead>
            <tr>
                <th style="text-align: left; border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 6px 0;">Item</th>
                <th style="text-align: right; border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 6px 0;">Qty</th>
                <th style="text-align: right; border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 6px 0;">Price</th>
                <th style="text-align: right; border-top: 1px dashed #9ca3af; border-bottom: 1px dashed #9ca3af; padding: 6px 0;">Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td style="padding: 6px 0;">
                        {{ $item->medicine?->name ?? '-' }}
                        <div style="font-size: 10px; color: #6b7280;">Batch: {{ $item->batch_no }}</div>
                    </td>
                    <td style="padding: 6px 0; text-align: right;">{{ $item->quantity }}</td>
                    <td style="padding: 6px 0; text-align: right;">{{ number_format((float) $item->price, 0) }}</td>
                    <td style="padding: 6px 0; text-align: right;">{{ number_format((float) $item->quantity * (float) $item->price, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="border-top: 1px dashed #9ca3af; padding-top: 8px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Discount</span>
            <strong>{{ number_format((float) $sale->discount, 0) }}</strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Tax</span>
            <strong>{{ number_format((float) $sale->tax, 0) }}</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 4px;">
            <span>Total</span>
            <strong>{{ number_format((float) $sale->total, 0) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Paid</span>
            <strong>{{ number_format((float) $sale->paid_amount, 0) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Change</span>
            <strong>{{ number_format((float) $sale->change, 0) }} MMK</strong>
        </div>
    </div>

    <div style="text-align: center; margin-top: 12px; border-top: 1px dashed #9ca3af; padding-top: 10px;">
        Thank you. Get well soon.
    </div>
</div>
