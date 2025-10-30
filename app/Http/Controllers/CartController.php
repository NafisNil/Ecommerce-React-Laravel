<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Service\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Illuminate\Support\Facades\DB;
class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CartService $cartService)
    {
        //
        return Inertia::render('Cart/Index', [
            'cart_items_grouped' => $cartService->getCartItemsGrouped(),
            // 'cart_total_quantity' => $cartService->getTotalQuantity(),
            // 'cart_total_price' => $cartService->getTotalPrice(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, CartService $cartService)
    {
        //
        $request->mergeIfMissing(['quantity' => 1]);
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'option_ids' => 'nullable|array',
        ]);
        $cartService->addItemToCart($product, $data['quantity'], $data['option_ids'] ?? null);
        // If this product exists in the user's wishlist, remove it after adding to cart
        if ($request->user()) {
            WishlistItem::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->delete();
        }
        return back()->with('success', 'Product added to cart successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, CartService $cartService)
    {
        //
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $optionIds = $request->input('option_ids', []);
        $quantity = $request->input('quantity', 1);
        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);
        return back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        //
        $optionIds = $request->input('option_ids', []);
        $cartService->removeItemFromCart($product->id, $optionIds);
        return back()->with('success', 'Product removed from cart successfully.');
    }

    public function checkout(Request $request, CartService $cartService)
    {
        Stripe::setApiKey(config('app.stripe_secret_key'));
        $vendorId = $request->input('vendor_id');
        $allCartItems = $cartService->getCartItemsGrouped();

        DB::beginTransaction();
        try {
            // Choose all groups or a single vendor group
            $groups = $allCartItems;
            if (!empty($vendorId)) {
                $groups = [];
                if (isset($allCartItems[$vendorId])) {
                    $groups[$vendorId] = $allCartItems[$vendorId];
                }
            }

            $orders = [];
            $lineItems = [];

            foreach ($groups as $group) {
                $user = $group['user'] ?? null;
                $cartItems = $group['items'] ?? [];
                $totalPrice = $group['total_price'] ?? 0;

                $order = Order::create([
                    'stripe_session_id' => null,
                    'user_id' => $request->user()->id,
                    'vendor_user_id' => $user['id'] ?? null,
                    'total_price' => $totalPrice,
                    'status' => OrderStatusEnum::Draft->value,
                ]);
                $orders[] = $order;

                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem['product_id'],
                        'quantity' => $cartItem['quantity'],
                        'price' => $cartItem['price'],
                        'variation_type_option_ids' => !empty($cartItem['option_ids'])
                            ? json_encode(array_values($cartItem['option_ids']))
                            : null,
                    ]);

                    $description = collect($cartItem['options'] ?? [])->map(function ($opt) {
                        $typeName = data_get($opt, 'variation_type.name');
                        $optName = data_get($opt, 'name');
                        return $typeName && $optName ? "$typeName : $optName" : null;
                    })->filter()->implode(', ');

                    $line = [
                        'price_data' => [
                            'currency' => config('app.currency', 'USD'),
                            'product_data' => [
                                'name' => $cartItem['title'] ?? 'Item',
                                'images' => [($cartItem['image'] ?? '')],
                            ],
                            'unit_amount' => (int) (($cartItem['price'] ?? 0) * 100), // Amount in cents
                        ],
                        'quantity' => $cartItem['quantity'] ?? 1,
                    ];
                    if (!empty($description)) {
                        $line['price_data']['product_data']['description'] = $description;
                    }
                    $lineItems[] = $line;
                }
            }

            $session = \Stripe\Checkout\Session::create([
                'customer_email' => $request->user()->email,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('stripe.success', []) . "?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => route('stripe.failure', []),
            ]);

            foreach ($orders as $order) {
                $order->stripe_session_id = $session->id;
                $order->save();
            }

            DB::commit();
            return redirect($session->url);
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage() ?: 'something went wrong!');
        }
    }

    public function clear(Request $request, CartService $cartService)
    {
        $cartService->clearCart();
        return back()->with('success', 'Cart cleared successfully.');
    }
}
