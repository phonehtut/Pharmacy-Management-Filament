<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sale Voucher #{{ $sale->id }}</title>
    <style>
        body {
            margin: 0;
            padding: 12px;
            background: #ffffff;
        }

        .print-toolbar {
            margin: 0 auto 10px;
            width: fit-content;
            display: flex;
            gap: 8px;
        }

        .print-toolbar button {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            cursor: pointer;
        }

        .print-toolbar button:hover {
            background: #f3f4f6;
        }

        @media print {
            @page {
                size: auto;
                margin: 6mm;
            }

            body {
                padding: 0;
            }

            .print-toolbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>

    @include('filament.invoices.sale-voucher', ['sale' => $sale])

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 180);
        });
    </script>
</body>
</html>
