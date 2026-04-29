<div style="font-family: Arial, sans-serif; color: #111827; background: #ffffff; padding: 20px; border-radius: 8px; font-size: 12px; line-height: 1.45;">
    <div style="margin-bottom: 16px; border-bottom: 1px solid #d1d5db; padding-bottom: 12px;">
        <h2 style="margin: 0 0 4px 0;">Purchase Invoice</h2>
        <div><strong>Invoice No:</strong> {{ $purchase->invoice_no }}</div>
        <div><strong>Date:</strong> {{ $purchase->purchased_at?->format('Y-m-d H:i') ?? '-' }}</div>
    </div>

    <div style="display: flex; gap: 24px; margin-bottom: 16px;">
        <div style="flex: 1;">
            <div style="font-weight: 600; margin-bottom: 6px;">Pharmacy</div>
            <div>{{ $purchase->branch?->pharmacy?->name ?? config('app.name') }}</div>
            <div>{{ $purchase->branch?->name ? 'Branch: '.$purchase->branch->name : '' }}</div>
            <div>{{ $purchase->branch?->location ?? '' }}</div>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 600; margin-bottom: 6px;">Supplier</div>
            <div>{{ $purchase->supplier?->name ?? '-' }}</div>
            <div>{{ $purchase->supplier?->phone ?? '' }}</div>
            <div>{{ $purchase->supplier?->email ?? '' }}</div>
            <div>{{ $purchase->supplier?->address ?? '' }}</div>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #ffffff;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Medicine</th>
                <th style="text-align: left; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Batch</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Expiry</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Qty</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Buy Price</th>
                <th style="text-align: right; border-bottom: 1px solid #9ca3af; padding: 8px 6px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb;">{{ $item->medicine?->name ?? '-' }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb;">{{ $item->batch_no }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                        {{ $item->expiry_date?->format('Y-m-d') ?? '-' }}
                    </td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $item->quantity }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ number_format((float) $item->buy_price, 2) }}</td>
                    <td style="padding: 8px 6px; border-bottom: 1px solid #e5e7eb; text-align: right;">
                        {{ number_format((float) $item->quantity * (float) $item->buy_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="width: 280px; margin-left: auto;">
        <div style="display: flex; justify-content: space-between; border-top: 1px solid #9ca3af; padding-top: 8px; margin-top: 8px;">
            <span>Total Amount</span>
            <strong>{{ number_format((float) $purchase->total_amount, 2) }} MMK</strong>
        </div>
    </div>
</div>
