<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;

class CityFunctionController extends Controller
{
    public function index()
    {
        // Order by category first, then name, so the grid view can render stable grouped sections.
        $cityFunctions = CityFunction::orderBy('category')->orderBy('name')->get();
        $categories = CityFunction::select('category')->distinct()->orderBy('category')->pluck('category');

        return view('grid', compact('cityFunctions', 'categories'));
    }
}
