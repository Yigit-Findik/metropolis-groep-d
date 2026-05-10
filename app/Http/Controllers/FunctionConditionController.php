<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use App\Models\FunctionCondition;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FunctionConditionController extends Controller
{
    public function store(Request $request, $cityFunctionId)
    {
        $cityFunction = CityFunction::findOrFail($cityFunctionId);

        $request->validate([
            'target_function_id' => 'required|exists:city_functions,id',
            'type' => 'required|in:required,forbidden',
        ]);

        $this->validateNoContradiction(
            $cityFunction,
            $request->input('target_function_id'),
            $request->input('type')
        );

        $condition = $cityFunction->functionConditions()->create([
            'target_function_id' => $request->input('target_function_id'),
            'type' => $request->input('type'),
        ]);

        return response()->json([
            'success' => true,
            'condition' => $condition,
        ], 201);
    }

    public function update(Request $request, $cityFunctionId, FunctionCondition $condition)
    {
        $cityFunction = CityFunction::findOrFail($cityFunctionId);
        abort_unless($condition->city_function_id === $cityFunction->id, 404);

        $request->validate([
            'target_function_id' => 'required|exists:city_functions,id',
            'type' => 'required|in:required,forbidden',
        ]);

        $this->validateNoContradiction(
            $cityFunction,
            $request->input('target_function_id'),
            $request->input('type'),
            $condition->id
        );

        $condition->update([
            'target_function_id' => $request->input('target_function_id'),
            'type' => $request->input('type'),
        ]);

        return response()->json([
            'success' => true,
            'condition' => $condition,
        ]);
    }

    private function validateNoContradiction(CityFunction $cityFunction, $targetFunctionId, $type, $ignoreConditionId = null)
    {
        $oppositeType = $type === 'required' ? 'forbidden' : 'required';

        $query = $cityFunction->functionConditions()
            ->where('target_function_id', $targetFunctionId)
            ->where('type', $oppositeType);

        if ($ignoreConditionId) {
            $query->where('id', '!=', $ignoreConditionId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'target_function_id' => ['A contradictory condition already exists for this target function.'],
            ]);
        }
    }

    public function destroy($cityFunctionId, FunctionCondition $condition)
    {
        $cityFunction = CityFunction::findOrFail($cityFunctionId);
        abort_unless($condition->city_function_id === $cityFunction->id, 404);

        $condition->delete();

        return response()->json(['success' => true]);
    }
}
