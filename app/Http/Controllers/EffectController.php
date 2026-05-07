<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;
use App\Services\CityFunctionEffectValueService;

class EffectController extends Controller
{
    public function __construct(
        private readonly CityFunctionEffectValueService $effectValueService,
    ) {
    }

    /**
     * Display the effects management table
     * Shows all functions (rows) vs all categories (columns)
     */
    public function index()
    {
        $functions = CityFunction::withTrashed()->orderBy('name')->get();

        return view('effects.index', [
            'functions' => $functions,
            'categories' => $this->effectValueService->effectColumns(),
            'selectedFunctionId' => request()->integer('function') ?: null,
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

        if ($function->trashed()) {
            abort(403);
        }
        
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
