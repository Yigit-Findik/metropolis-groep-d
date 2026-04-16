<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;

class LibraryController extends Controller
{
    public function index()
    {
        $cityFunctions = CityFunction::orderBy('category')->orderBy('name')->get();

        return view('library', compact('cityFunctions'));
    }
}
