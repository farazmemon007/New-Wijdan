<!DOCTYPE html>
<html>
<head>
    <title>Product Barcode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .label {
            border: 2px dashed #000;
            width: 300px;
            padding: 20px;
            text-align: center;
        }
        .barcode {
            margin: 20px 0;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
        }
        .price {
            font-size: 16px;
            margin-top: 10px;
        }
        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="label">
        <div class="product-name">{{ $product->item_name }}</div>
        <div class="barcode">
            {!! DNS1D::getBarcodeHTML($product->item_code, 'C128') !!}
        </div>
        <div class="product-code">{{ $product->item_code }}</div>
        <div class="price">PKR {{ $product->retail_price }}</div>
    </div>

    {{-- <button class="print-btn" onclick="window.print()">🖨️ Print Barcode</button> --}}

</body>
</html>
