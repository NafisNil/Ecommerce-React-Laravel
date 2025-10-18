<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Order Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .order-info {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Order Received!</h1>
    </div>

    <div class="order-info">
        <h2>Order #{{ $order->order_number ?? $order->id }}</h2>
        
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
        <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
        @if($order->vendorUser)
            <p><strong>Vendor:</strong> {{ $order->vendorUser->vendor->shop_name ?? $order->vendorUser->name }}</p>
        @endif
    </div>

    <h3>Order Summary</h3>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->title }}</strong>
                        @if($item->variation_type_option_ids)
                            @php
                                $optionIds = is_string($item->variation_type_option_ids) 
                                    ? json_decode($item->variation_type_option_ids, true) 
                                    : $item->variation_type_option_ids;
                                if ($optionIds) {
                                    $options = \App\Models\VariationTypeOption::whereIn('id', $optionIds)
                                        ->with('variationType')
                                        ->get();
                                }
                            @endphp
                            @if(isset($options) && $options->count() > 0)
                                <br><small style="color: #666;">
                                    @foreach($options as $option)
                                        {{ $option->variationType->name ?? 'Option' }}: {{ $option->name }}
                                        @if(!$loop->last), @endif
                                    @endforeach
                                </small>
                            @endif
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>Order Total:</strong></td>
                <td class="text-right"><strong>${{ number_format($order->total_price ?? $order->total_amount ?? 0, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="order-info">
        <h3>Order Status</h3>
        <p><strong>Status:</strong> {{ \App\Enums\OrderStatusEnum::labels()[$order->status->value] ?? ucfirst($order->status->value ?? $order->status) }}</p>
        @if($order->stripe_session_id)
            <p><strong>Payment:</strong> Processed via Stripe</p>
        @endif
    </div>

    <div class="footer">
        <p>Thank you for your order!</p>
        <p><strong>{{ config('app.name') }}</strong></p>
    </div>
</body>
</html>