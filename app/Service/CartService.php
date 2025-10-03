<?php

namespace App\Service;
use App\Models\Product;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Arr;

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
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            }else {
                // Logic to fetch cart items from database or cookies
                $this->cacheCartItems = []; // Replace with actual fetched items
                return $this->cacheCartItems;

            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getTotalQuantity()
    {
        // Logic to get total quantity of items in the cart
    }

    public function getTotalPrice()
    {
        // Logic to get total price of items in the cart
    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to update item quantity in the database
    }

    protected function updateItemQuantityInCookie(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to update item quantity in cookies
    }

    protected function saveItemToDatabase(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to save item to the database
    }

    protected function saveItemToCookie(int $productId, int $quantity, array $optionIds = null)
    {
        // Logic to save item to cookies
    }

    protected function removeItemFromDatabase(int $productId, array $optionIds = null)
    {
        // Logic to remove item from the database
    }

    protected function removeItemFromCookie(int $productId, array $optionIds = null)
    {
        // Logic to remove item from cookies
    }

    protected function getCartItemFromDatabase(int $productId, array $optionIds = null)
    {
        // Logic to get item from the database
    }

    protected function getCartItemFromCookie(int $productId, array $optionIds = null)
    {
        // Logic to get item from cookies
    }
}
