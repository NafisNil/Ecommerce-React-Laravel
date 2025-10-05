<?php

namespace App\Service;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Container\Attributes\DB;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;

class CartService
{
    /**
     * Create a new class instance.
     */
    private ?array $cacheCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_EXPIRATION = 60 * 24 * 30; // 30 days
    public function __construct()
    {
        //
    }

    public function addItemToCart(Product $product, int $quantity = 1, array $optionIds = null)
    {
        // Logic to add product to cart
        if ($optionIds === null) {
            $optionIds = $product->variation_types->mapWithKeys(fn(VariationType $variationType) => [
                $variationType->id => $variationType->options->first()->id
            ])->toArray();
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to update product in cart

    }

    public function removeItemFromCart(int $productId, array $optionIds = null)
    {
        // Logic to remove product from cart
    }

    public function getCartItems()
    {
        // Logic to get all items in the cart
        try {
            //code...
            if ($this->cacheCartItems !== null) {
                # code...
                if (Auth::check()) {
                    # code...
                    $cartItems = $this->getCartItemFromDatabase();
                }else {
                    # code...
                    $cartItems = $this->getCartItemFromCookie();
                }
                // 
                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);
                $products = Product::whereIn('id', $productIds)->with('user.vendor')->forWebsite()->get()->keyBy('id');

                $cartItemData = [];
                foreach ($cartItems as $item) {
                    $product = data_get($products, $item['product_id']);
                    if (!$product) {
                        # code...
                        continue;
                    }
                    $optionInfo = [];
                    $options = VariationTypeOption::with('variationType')->whereIn('id', $item['option_ids'] ?? [])->get()->keyBy('id');

                    $imageUrl = null;

                    foreach ($item['option_ids'] ?? [] as $optionId) {
                        $option = data_get($options, $optionId);
                        if (!$imageUrl) {
                            # code...
                            $imageUrl = $option->getFirstMediaUrl('option_images', 'thumb') ?: null;
                        }
                        $optionInfo[] = [
                            'id' => $optionId,
                            'name' => $option->name,
                            'variation_type' => [
                                'id' => $option->variationType->id,
                                'name' => $option->variationType->name,
                            ]
                        ];
                    }

                    $cartItemData[]=[
                        'id' => $item['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'option_ids' => $item['option_ids'] ?? [],
                        'options' => $optionInfo,
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('product_images', 'thumb') ?: null,
                        'user' =>[
                            'id' => $product->created_by,
                            'name' => $product->user->vendor->store_name,

                        ]
                    ];

                }

            }else {
                // Logic to fetch cart items from database or cookies
                $this->cacheCartItems = []; // Replace with actual fetched items
                return $this->cacheCartItems;

            }
            return $this->cacheCartItems;
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Error fetching cart items: ' . $th->getMessage());
        }

        return [];
    }

    public function getTotalQuantity()
    {
        // Logic to get total quantity of items in the cart
        $totalQuantity = 0;
        foreach ($this->getCartItems() as $key => $item) {
            # code...
            $totalQuantity += $item['quantity'];
        }
        return $totalQuantity;
    }

    public function getTotalPrice()
    {
        // Logic to get total price of items in the cart
        $total =0;
        foreach ($this->getCartItems() as $key => $item) {
            # code...
            $total += $item['quantity'] * $item['price'];
        }
        return $total;
    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to update item quantity in the database
        $userId = Auth::id();
        $cartItem = CartItem::where('user_id', $userId)->where('product_id', $productId)->where('variation_types_option_ids', json_encode($optionIds))->first();

        if ($cartItem) {
            # code...
            $cartItem->update(
                ['quantity' => $quantity]
            );
        }
    }

    protected function updateItemQuantityInCookie(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to update item quantity in cookies
        $cartItems = $this->getCartItemFromCookie();
        ksort($optionIds);
        $itemKey = $productId . '-' . json_encode( $optionIds ?? []);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_EXPIRATION);

    }

    protected function saveItemToDatabase(int $productId, int $quantity, $price, array $optionIds = null)
    {
        // Logic to save item to the database
           $userId = Auth::id();
           ksort($optionIds);
           $cartItem = CartItem::where('user_id', $userId)->where('product_id', $productId)->where('variation_types_option_ids', json_encode($optionIds))->first();

           if ($cartItem) {
            # code...
            $cartItem->update([
                'quantity' => DB::raw('quantity + ' . $quantity)
            ]);
           } else {
            # code...
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'variation_types_option_ids' => json_encode($optionIds),
                'quantity' => $quantity,
                'price' => $price,
            ]);
           }
           
    }

    protected function saveItemToCookie(int $productId, int $quantity,  $price, array $optionIds = null)
    {
        // Logic to save item to cookies
        $cartItems = $this->getCartItemFromCookie();
        ksort($optionIds);
        $itemKey = $productId . '-' . json_encode( $optionIds ?? []);
        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] += $quantity;
            $cartItems[$itemKey]['price'] = $price; // Update price if needed
        } else {
            # code...
            $cartItems[$itemKey] = [
                'id' => uniqid(),
                'product_id' => $productId,
                'option_ids' => $optionIds ?? [],
                'quantity' => $quantity,
                'price' => $price,
            ];
        }
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_EXPIRATION);
        
    }

    protected function removeItemFromDatabase(int $productId, array $optionIds = null)
    {
        // Logic to remove item from the database
        $userId = Auth::id();
        ksort($optionIds);
        CartItem::where('user_id', $userId)->where('product_id', $productId)->where('variation_types_option_ids', json_encode($optionIds))->delete();
    }

    protected function removeItemFromCookie(int $productId, array $optionIds = null)
    {
        // Logic to remove item from cookies
        $cartItems = $this->getCartItemFromCookie();
        ksort($optionIds);
        $itemKey = $productId . '-' . json_encode( $optionIds ?? []);
        unset($cartItems[$itemKey]);
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_EXPIRATION);
    }

    protected function getCartItemFromDatabase(int $productId, array $optionIds = null)
    {
        // Logic to get item from the database
        $userId = Auth::id();
        $cartItems = CartItem::where('user_id', $userId)->get()->map(
            function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'option_ids' => $item->variation_types_option_ids,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            }
        )->toArray();
        return $cartItems;
    }

    protected function getCartItemFromCookie()
    {
        // Logic to get item from cookies
        $cartItems = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
        return $cartItems;
    }
}
