<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;

class EffectController extends Controller
{
    /**
     * Display the effects management table
     * Shows all functions (rows) vs all categories (columns)
     */
    public function index()
    {
        $functions = CityFunction::all();
        
        // get all unique categories from the functions
        $categories = ['Safety', 'Recreation', 'Environment Quality', 'Facilities', 'Mobility'];

        return view('effects.index', [
            'functions' => $functions,
            'categories' => $categories,
        ]);
    }

    /**
     * Update an effect value for a function and category
     * EFF.1 - Allow inline editing of effect values
     */
    public function update(Request $request, $functionId)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'value' => 'required|integer|min:-10|max:10',
        ]);

        $function = CityFunction::findOrFail($functionId);
        
        // Update the category column with the new value
        $function->update([
            $validated['category'] => $validated['value'],
        ]);

        return response()->json([
            'message' => 'Effect updated successfully',
            'value' => $validated['value'],
            'function' => $function,
        ]);
    }
}
