<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $productIds = WishlistItem::query()
            ->where('user_id', $user->id)
            ->pluck('product_id');

        $products = Product::query()
            ->forWebsite()
            ->whereIn('products.id', $productIds)
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Wishlist/Index', [
            'products' => ProductListResource::collection($products),
        ]);
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();
        $existing = WishlistItem::query()->where('user_id', $user->id)->where('product_id', $product->id)->first();
        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Removed from wishlist');
        }

        WishlistItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Added to wishlist');
    }
}
