<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;

class LibraryController extends Controller
{
    public function index()
    {
        $cityFunctions = CityFunction::orderBy('category')->orderBy('name')->get();
        $categories = CityFunction::select('category')->distinct()->orderBy('category')->pluck('category');

        return view('library', compact('cityFunctions', 'categories'));
    }
}
