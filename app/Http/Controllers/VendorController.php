<?php

namespace App\Http\Controllers;

use App\Enums\VendorStatusEnum;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\RolesEnum;
use App\Models\Product;
use Inertia\Inertia;
use App\Http\Resources\ProductListResource;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    //
    public function profile(Vendor $vendor)
    {
        $products = Product::query()->forWebsite()->where('created_by', $vendor->user_id)->paginate(12);
        return Inertia::render('Vendor/Profile', [
            'vendor' => $vendor,
            'products' => ProductListResource::collection($products),
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_name' =>['required', 'regex:/^[a-z0-9-]+$/', 'unique:vendors,shop_name,'.$request->user()->id.',user_id', 'max:50'],
            'shop_address' => ['required', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ],[
            'shop_name.regex' => 'The shop name may only contain lowercase letters, numbers, and hyphens.',
            'shop_name.unique' => 'The shop name has already been taken.',
        ]);

        $user = $request->user();
        $vendor = $user->vendor ?? new Vendor();
        $vendor->user_id = $user->id;
        $vendor->shop_name = $request->input('shop_name');
        $vendor->shop_address = $request->input('shop_address');
        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('vendor-covers', 'public');
            if (!empty($vendor->cover_image)) {
                Storage::disk('public')->delete($vendor->cover_image);
            }
            $vendor->cover_image = $path;
        }

        $vendor->status = VendorStatusEnum::Approved;
        $vendor->save();

        $user->assignRole(RolesEnum::Vendor);

        return redirect()->back()->with('success', 'Vendor profile updated successfully.');
    }
}
