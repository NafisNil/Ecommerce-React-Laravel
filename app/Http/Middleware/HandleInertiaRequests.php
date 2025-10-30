<?php

namespace App\Http\Middleware;

use App\Http\Resources\AuthUserResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Service\CartService;
use App\Http\Resources\DepartmentResource;
use App\Models\WishlistItem;
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cartService = app(CartService::class);
        $totalQuantity = $cartService->getTotalQuantity();
        $totalPrice = $cartService->getTotalPrice();
    $cartItems = $cartService->getCartItems();
        $departments = Department::published()->with('categories')->orderBy('created_at', 'desc')->limit(10)->get();

        return [
            ...parent::share($request),
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $request->user() ? new AuthUserResource($request->user()) : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'info' => session('info'),
            ],
            'cart_items' => $cartItems,
            'cart_total_quantity' => $totalQuantity,
            'cart_total_price' => $totalPrice,
            'departments' => DepartmentResource::collection($departments)->toArray($request),
            'keyword' => $request->input('keyword', null),
            'wishlist_count' => fn () => $request->user() ? WishlistItem::where('user_id', $request->user()->id)->count() : 0,
            'wishlist_product_ids' => fn () => $request->user() ? WishlistItem::where('user_id', $request->user()->id)->pluck('product_id')->toArray() : [],
        ];
    }
}
