<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::withCount('orderItems')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(function ($o) {
                return [
                    'id' => $o->id,
                    'status' => (string) $o->status,
                    'total_price' => (float) $o->total_price,
                    'items_count' => (int) $o->order_items_count,
                    'created_at' => $o->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
