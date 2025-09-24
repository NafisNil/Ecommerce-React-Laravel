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
        return Inertia::render('Product/Show', [
            'product' => new ProductResource($product),
            'variationOptions' => request()->input('options', [])
        ]);
    }
}
