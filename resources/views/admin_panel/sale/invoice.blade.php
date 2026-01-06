
@extends('admin_panel.layout.app')

@section('content')
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 2px solid #000;
            font-size: 14px;
            line-height: 20px;
            color: #000;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table th {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .invoice-box table td {
            border: 1px solid #000;
            text-align: center;
        }

        .invoice-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .no-border {
            border: none !important;
        }

        .signature {
            margin-top: 60px;
        }

        .signature span {
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 5px;
        }
    </style>

    <div class="invoice-box">
        <div class="invoice-title">Ameer & Sons</div>
        <p class="text-center">Electronics and Home Applience Dealer<br>Main Road, City Name</p>

        <table style="margin-top: 20px;">
            <tr>
                <td class="no-border"><strong>Invoice No:</strong> {{ $sale->invoice_no }}</td>
                <td class="no-border text-end"><strong>Date:</strong> {{ $sale->created_at->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td class="no-border"><strong>Customer:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}</td>
                <td class="no-border text-end"><strong>Phone:</strong> {{ $sale->tel ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border"><strong>Address:</strong> {{ $sale->address ?? '-' }}</td>
            </tr>
        </table>

        <table style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Particular</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Discount</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->item_name ?? 'N/A' }}</td>
                    <td>{{ $item->sales_qty }}</td>
                    <td>{{ number_format($item->retail_price, 2) }}</td>
                    <td>{{ number_format($item->discount_amount, 2) }}</td>
                    <td>{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>{{ number_format($sale->total_balance, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="signature text-end">
            <span>Authorized Signature</span>
        </div>
    </div>
@endsection
