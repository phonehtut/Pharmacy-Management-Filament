<div style="font-family: Arial, sans-serif; color: #111827; background: #ffffff; padding: 20px; border-radius: 8px; font-size: 12px; line-height: 1.45;">
    <div style="margin-bottom: 16px; border-bottom: 1px solid #d1d5db; padding-bottom: 12px;">
        <h2 style="margin: 0 0 4px 0;">Sale Invoice</h2>
        <div><strong>Voucher:</strong> #{{ $sale->id }}</div>
        <div><strong>Date:</strong> {{ $sale->sold_at?->format('Y-m-d H:i') ?? '-' }}</div>
    </div>

    <div style="display: flex; gap: 24px; margin-bottom: 16px;">
        <div style="flex: 1;">
            <div style="font-weight: 600; margin-bottom: 6px;">Pharmacy</div>
            <div>{{ $sale->branch?->pharmacy?->name ?? config('app.name') }}</div>
            <div>{{ $sale->branch?->name ? 'Branch: '.$sale->branch->name : '' }}</div>
            <div>{{ $sale->branch?->location ?? '' }}</div>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 600; margin-bottom: 6px;">Customer</div>
            <div>{{ $sale->customer?->name ?? 'Walk-in Customer' }}</div>
            <div>{{ $sale->customer?->phone ?? '' }}</div>
            <div>{{ $sale->customer?->address ?? '' }}</div>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #ffffff;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Medicine</th>
                <th style="text-align: left; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Batch</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Qty</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Price</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb;">{{ $item->medicine?->name ?? '-' }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb;">{{ $item->batch_no }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $item->quantity }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ number_format((float) $item->price, 2) }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                        {{ number_format((float) $item->quantity * (float) $item->price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="width: 280px; margin-left: auto;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span>Subtotal</span>
            <strong>{{ number_format((float) $sale->total + (float) $sale->discount - (float) $sale->tax, 2) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span>Discount</span>
            <strong>{{ number_format((float) $sale->discount, 2) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span>Tax</span>
            <strong>{{ number_format((float) $sale->tax, 2) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid #9ca3af; padding-top: 8px; margin-top: 8px;">
            <span>Total</span>
            <strong>{{ number_format((float) $sale->total, 2) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 4px;">
            <span>Paid</span>
            <strong>{{ number_format((float) $sale->paid_amount, 2) }} MMK</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 4px;">
            <span>Change</span>
            <strong>{{ number_format((float) $sale->change, 2) }} MMK</strong>
        </div>
    </div>

    <div style="margin-top: 28px; color: #4b5563;">
        Served by {{ $sale->user?->name ?? '-' }}
    </div>
</div>
