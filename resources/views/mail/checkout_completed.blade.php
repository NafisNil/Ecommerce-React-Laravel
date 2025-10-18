<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You for Your Order</title>
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
            background: #28a745;
            color: white;
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
        .next-steps {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-separator {
            border-bottom: 2px solid #dee2e6;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank You for Your Order!</h1>
    </div>

    <p>Hello {{ $orders->first()->user->name ?? 'Customer' }},</p>
    <p>Thank you for your purchase! Your order has been confirmed and is being processed.</p>

    @foreach($orders as $order)
        @if(!$loop->first)
            <div class="order-separator"></div>
        @endif
        
        <div class="order-info">
            <h2>Order #{{ $order->order_number ?? $order->id }}</h2>
            
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
            @if($order->vendorUser)
                <p><strong>Vendor:</strong> {{ $order->vendorUser->vendor->shop_name ?? $order->vendorUser->name }}</p>
            @endif
            <p><strong>Status:</strong> <span style="color: #28a745;">{{ \App\Enums\OrderStatusEnum::labels()[$order->status->value] ?? ucfirst($order->status->value ?? $order->status) }}</span></p>
        </div>

        <h3>Order Items</h3>

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
    @endforeach

    <div class="next-steps">
        <h3>What's Next?</h3>
        <ul>
            <li>You will receive tracking information once your order ships</li>
            <li>If you have any questions, please contact our support team</li>
            <li>You can view your order history in your account dashboard</li>
        </ul>
    </div>

    <div class="footer">
        <p>Thank you for choosing us!</p>
        <p><strong>{{ config('app.name') }}</strong></p>
    </div>
</body>
</html>