
<!DOCTYPE html>
<html>
<head>
    <title>Product Barcode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .label {
            border: 1px solid #000;
            padding: 10px;
            width: 280px;
            text-align: center;
        }
        .brand-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .barcode {
            margin: 5px 0;
        }
        .product-info {
            font-size: 13px;
            margin-top: 2px;
        }
        .price {
            font-size: 15px;
            font-weight: bold;
            margin-top: 8px;
        }
        @media print {
            body {
                height: auto;
            }
        }
    </style>
</head>
<body>

<div class="label">
    <div class="brand-name" style="letter-spacing: 2px">WIJDAN</div>

    <div class="barcode" style="display: flex; justify-content: center;">
       {!! DNS1D::getBarcodeHTML($discount->product->item_code, 'C128', 1.4, 40) !!}
    </div>

    <div class="product-info" style="font-size: 15px; font-weight: bold;">
     <span > {{ $discount->product->item_name }} </span>
    </div>

    <div class="price">PRICE : <s>{{ $discount->product->price }}</s></div>
    {{--  <div class="price">DISCOUNT : {{ $discount->discount_percentage . '%'}}</div>  --}}
    <div class="price">SALE PRICE : {{ $discount->final_price }}</div>
</div>


</body>
</html>
