<?php

namespace App\Service;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;

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
            // Load variation types with options if not already available
            $variationTypes = $product->relationLoaded('variationTypes')
                ? $product->variationTypes
                : $product->variationTypes()->with('options')->get();

            $optionIds = $variationTypes
                ->mapWithKeys(function (VariationType $variationType) {
                    $firstOption = optional($variationType->options)->first();
                    return $firstOption ? [$variationType->id => $firstOption->id] : [];
                })
                ->toArray();
        }
        $price = $product->getPriceForOptions($optionIds);
        if (Auth::check()) {
            # code...
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        }else {
            # code...
            $this->saveItemToCookie($product->id, $quantity, $price, $optionIds);
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to update product in cart
        if (Auth::check()) {
            # code...
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        }else {
            # code...
            $this->updateItemQuantityInCookie($productId, $quantity, $optionIds);
        }

    }

    public function removeItemFromCart(int $productId, array $optionIds = null)
    {
        // Logic to remove product from cart
        if (Auth::check()) {
            # code...
            $this->removeItemFromDatabase($productId, $optionIds);
        }else {
            # code...
            $this->removeItemFromCookie($productId, $optionIds);
        }
    }

    public function getCartItems()
    {
        // Logic to get all items in the cart
        try {
            //code...
            if ($this->cacheCartItems === null) {
                // Build and cache cart items once per request
                $cartItems = Auth::check()
                    ? $this->getCartItemFromDatabase()
                    : $this->getCartItemFromCookie();

                $productIds = collect($cartItems)->pluck('product_id')->unique()->all();
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');

                $cartItemData = [];
                foreach ($cartItems as $item) {
                    $product = data_get($products, $item['product_id']);
                    if (!$product) {
                        continue;
                    }

                    $optionIds = is_array($item['option_ids'] ?? null) ? $item['option_ids'] : [];
                    $options = !empty($optionIds)
                        ? VariationTypeOption::with('variationType')->whereIn('id', $optionIds)->get()->keyBy('id')
                        : collect();

                    $imageUrl = null;
                    $optionInfo = [];
                    foreach ($optionIds as $optionId) {
                        $option = $options->get($optionId);
                        if ($option) {
                            if (!$imageUrl) {
                                $imageUrl = $option->getFirstMediaUrl('option_images', 'thumb') ?: null;
                            }
                            $optionInfo[] = [
                                'id' => $optionId,
                                'name' => $option->name,
                                'variation_type' => [
                                    'id' => optional($option->variationType)->id,
                                    'name' => optional($option->variationType)->name,
                                ]
                            ];
                        }
                    }

                    $cartItemData[] = [
                        'id' => $item['id'] ?? null,
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'option_ids' => $optionIds,
                        'options' => $optionInfo,
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('products', 'thumb') ?: null,
                        'user' => [
                            'id' => $product->created_by,
                            'name' => optional(optional($product->user)->vendor)->store_name,
                        ]
                    ];
                }

                $this->cacheCartItems = $cartItemData;
            }

            return $this->cacheCartItems ?? [];
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
    $optionIds = $optionIds ?? [];
    // sort by numeric keys to ensure consistent JSON
    if (!empty($optionIds)) { ksort($optionIds, SORT_NUMERIC); }
        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_types_option_ids', json_encode($optionIds))
            ->first();

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
    $optionIds = $optionIds ?? [];
    if (!empty($optionIds)) { ksort($optionIds, SORT_NUMERIC); }
        $itemKey = $productId . '-' . json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_EXPIRATION);

    }

    protected function saveItemToDatabase(int $productId, int $quantity, $price, array $optionIds = null)
    {
        // Logic to save item to the database
           $userId = Auth::id();
           $optionIds = $optionIds ?? [];
           if (!empty($optionIds)) { ksort($optionIds, SORT_NUMERIC); }
           $cartItem = CartItem::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('variation_types_option_ids', json_encode($optionIds))
                ->first();

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
    $optionIds = $optionIds ?? [];
    if (!empty($optionIds)) { ksort($optionIds, SORT_NUMERIC); }
        $itemKey = $productId . '-' . json_encode($optionIds);
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
    $optionIds = $optionIds ?? [];
    if (!empty($optionIds)) { ksort($optionIds, SORT_NUMERIC); }
        CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_types_option_ids', json_encode($optionIds))
            ->delete();
    }

    protected function removeItemFromCookie(int $productId, array $optionIds = null)
    {
        // Logic to remove item from cookies
        $cartItems = $this->getCartItemFromCookie();
        $optionIds = $optionIds ?? [];
        ksort($optionIds);
        $itemKey = $productId . '-' . json_encode($optionIds);
        unset($cartItems[$itemKey]);
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_EXPIRATION);
    }

    protected function getCartItemFromDatabase()
    {
        // Logic to get item from the database
        $userId = Auth::id();
        $cartItems = CartItem::where('user_id', $userId)->get()->map(
            function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'option_ids' => json_decode($item->variation_types_option_ids, true) ?: [],
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
        return is_array($cartItems) ? $cartItems : [];
    }

    public function getCartItemsGrouped(){
        $cartItems = $this->getCartItems();
        return collect($cartItems)->groupBy(fn ($item) => $item['user']['id'])
        ->map(fn ($items,  $userId) => [
            'user'=>$items->first()['user'],
            'items' => $items->toArray(),
            'total_quantity' => $items->sum('quantity'),
            'total_price' => $items->sum(fn($item) => $item['quantity'] * $item['price']),
        ])->toArray();
    }

    public function clearCart(): void
    {
        if (Auth::check()) {
            $userId = Auth::id();
            CartItem::where('user_id', $userId)->delete();
        }
        // Clear cookie cart in both cases to keep consistent when logging out
        Cookie::queue(self::COOKIE_NAME, json_encode([]), self::COOKIE_EXPIRATION);
        // Reset cached items
        $this->cacheCartItems = null;
    }

    public function moveCartItemToDatabase($userId){
        $cartItems = $this->getCartItemFromCookie();
        foreach ($cartItems as $item) {
            # code...
           $existingItem = CartItem::where('user_id', $userId)
                ->where('product_id', $item['product_id'])
                ->where('variation_types_option_ids', json_encode($item['option_ids'] ?? []))
                ->first();
              if ($existingItem) {
                # code...
                $existingItem->update([
                    'quantity' =>  $existingItem->quantity + $item['quantity'],
                    'price' => $item['price'],
                ]);
              } else {
                # code...
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $item['product_id'],
                    'variation_types_option_ids' => json_encode($item['option_ids'] ?? []),
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
              }
        }
        // Clear cookie cart after moving to database
        Cookie::queue(self::COOKIE_NAME, json_encode([]), self::COOKIE_EXPIRATION);
        // Reset cached items
        $this->cacheCartItems = null;
    }
}
