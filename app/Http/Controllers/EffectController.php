<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\CityFunctionEffectValueService;

class EffectController extends Controller
{
    /**
     * Display the effects management table
     * Shows all functions (rows) vs all categories (columns)
     */
    public function index(CityFunctionEffectValueService $effectValueService)
    {
        $functions = CityFunction::withTrashed()->orderBy('name')->get();

        return view('effects.index', [
            'functions' => $functions,
            'categories' => $effectValueService->effectColumns(),
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
            'category' => ['required', 'string', Rule::in(CityFunction::EFFECT_COLUMNS)],
            'value'    => 'required|integer|min:-10|max:10',
        ]);

        $function = CityFunction::findOrFail($functionId);

        if ($function->trashed()) {
            abort(403);
        }
        
        $function->update([$validated['category'] => $validated['value']]);

        return response()->json([
            'message' => 'Effect updated successfully',
            'value' => $validated['value'],
            'function' => $function,
        ]);
    }
}
