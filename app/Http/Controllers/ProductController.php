<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    //
    public function index(Request $request){
        $keyword = $request->input('keyword', null);
        $products = Product::query()
            ->forWebsite()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Welcome', [
            'products' => ProductListResource::collection($products),
            'keyword' => $keyword,
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

    public function productByDepartment(Request $request, Department $department){
        abort_if(!$department->exists, 404);
        $keyword = $request->input('keyword', null);
        $products = Product::query()->forWebsite()->where('department_id', $department->id)->when($keyword, function ($query, $keyword) {
            $query->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
        })->paginate(12);

        return Inertia::render('Department/Index', [
            'products' => ProductListResource::collection($products),
            'keyword' => $keyword,
            'department' => new DepartmentResource($department)
        ]);
    }
}
