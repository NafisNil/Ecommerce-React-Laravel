<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminSliderController extends Controller
{
    public function destroy(Request $request, Slider $slider): RedirectResponse
    {
        // Optionally ensure the user is authorized to delete
        // $this->authorize('delete', $slider);

        $slider->delete();
        return back()->with('success', 'Slide deleted');
    }
}
