<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    //
    public function index(){
        $products = Product::query()->published()->paginate(12);
        return Inertia::render('Welcome', [
            'products' => ProductListResource::collection($products)
        ]);
    }

    public function show(Product $product){
        // Ensure all needed relations & media are eager loaded to prevent N+1 and guarantee presence
        $product->load([
            'variationTypes.options.media', // options with media
            'media', // product images
            'variations',
            'department',
            'user'
        ]);

        $productPayload = (new ProductResource($product))->toArray(request());

        return Inertia::render('Product/Show', [
            'product' => $productPayload,
            'variationOptions' => request()->input('options', [])
        ]);
    }
}
