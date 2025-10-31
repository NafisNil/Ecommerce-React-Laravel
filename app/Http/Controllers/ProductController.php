<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Department;
use App\Models\Slider;
use App\Models\ProductRating;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
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

        $sliders = Slider::active()->orderBy('sort_order')->get()->map(fn($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'subtitle' => $s->subtitle,
            'link_url' => $s->link_url,
            'image_url' => $s->image_url,
        ]);

        // Featured & Offered sections for homepage
    $featured = Product::query()->forWebsite()->where('is_featured', true)->latest('id')->take(12)->get();
    $offered = Product::query()->forWebsite()->where('is_offered', true)->latest('id')->take(12)->get();

        return Inertia::render('Welcome', [
            'products' => ProductListResource::collection($products),
            'keyword' => $keyword,
            'sliders' => $sliders,
            'featured' => $featured->map(fn($p) => (new ProductListResource($p))->toArray($request))->all(),
            'offered' => $offered->map(fn($p) => (new ProductListResource($p))->toArray($request))->all(),
        ]);
    }


    public function shop(Request $request){
        $keyword = $request->input('keyword');
        $departmentId = $request->input('department_id');
        $categoryId = $request->input('category_id');
        $featured = $request->boolean('featured');
        $offered = $request->boolean('offered');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $products = Product::query()
            ->forWebsite()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($featured, fn($q) => $q->where('is_featured', true))
            ->when($offered, fn($q) => $q->where('is_offered', true))
            ->when($minPrice !== null && $minPrice !== '', fn($q) => $q->where('price', '>=', (float)$minPrice))
            ->when($maxPrice !== null && $maxPrice !== '', fn($q) => $q->where('price', '<=', (float)$maxPrice))
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Shop/Index', [
            'products' => ProductListResource::collection($products),
            'keyword' => $keyword,
            'filters' => [
                'department_id' => $departmentId,
                'category_id' => $categoryId,
                'featured' => $featured,
                'offered' => $offered,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
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

        // Ratings summary
        $avg = round((float) ($product->ratings()->avg('rating') ?? 0), 2);
        $count = (int) $product->ratings()->count();
        $userRating = Auth::check() ? (int) (ProductRating::query()
            ->where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->value('rating') ?? 0) : 0;

        $productPayload['average_rating'] = $avg;
        $productPayload['ratings_count'] = $count;
        $productPayload['user_rating'] = $userRating;

        return Inertia::render('Product/Show', [
            'product' => $productPayload,
            'variationOptions' => request()->input('options', [])
        ]);
    }

    public function rate(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => ['required','integer','min:1','max:5'],
            'comment' => ['nullable','string','max:2000'],
        ]);

        $userId = Auth::id();
        abort_if(!$userId, 403);

        ProductRating::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $product->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return back()->with('success', 'Thanks for your rating!');
    }

    public function productByDepartment(Request $request, Department $department){
        abort_if(!$department->exists, 404);
        $keyword = $request->input('keyword', null);
        $categorySlug = $request->input('category', null);
        // Normalize category: treat missing, empty, 'null', or 'all' as no filter
        if (is_string($categorySlug)) {
            $categorySlug = trim($categorySlug);
            if ($categorySlug === '' || strtolower($categorySlug) === 'null' || strtolower($categorySlug) === 'all') {
                $categorySlug = null;
            }
        }

        $products = Product::query()
            ->forWebsite()
            ->where('department_id', $department->id)
            ->when($categorySlug, function ($query) use ($categorySlug, $department) {
                // Filter by category slug, ensuring it belongs to this department
                $query->whereHas('category', function ($q) use ($categorySlug, $department) {
                    $q->where('slug', $categorySlug)
                      ->where('department_id', $department->id);
                });
            })
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            })
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Department/Index', [
            'products' => ProductListResource::collection($products),
            'keyword' => $keyword,
            'department' => new DepartmentResource($department),
            'category' => $categorySlug,
        ]);
    }
}
