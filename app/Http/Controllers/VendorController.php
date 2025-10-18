<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
class VendorController extends Controller
{
    //
    public function profile(Vendor $vendor)
    {
        return view('vendor.profile', [
            'vendor' => $vendor,
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
        ]);

        $vendor = Vendor::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'shop_name' => $request->shop_name,
                'shop_address' => $request->shop_address,
                'shop_description' => $request->shop_description,
            ]
        );

        return redirect()->back()->with('success', 'Vendor profile updated successfully.');
    }
}
