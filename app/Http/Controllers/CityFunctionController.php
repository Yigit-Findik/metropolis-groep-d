<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CityFunctionController extends Controller
{
    public function index()
    {
        $cityFunctions = CityFunction::with('functionConditions')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $categories = CityFunction::select('category')->distinct()->orderBy('category')->pluck('category');

        return view('city_functions', compact('cityFunctions', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string|max:255',
            'description'         => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'safety'              => 'nullable|integer|min:0',
            'recreation'          => 'nullable|integer|min:0',
            'environment_quality' => 'nullable|integer|min:0',
            'facilities'          => 'nullable|integer|min:0',
            'mobility'            => 'nullable|integer|min:0',
        ]);

        $imagePath = '';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/city_functions'), $filename);
            $imagePath = 'images/city_functions/' . $filename;
        }

        CityFunction::create([
            'name'                => $request->name,
            'category'            => $request->category,
            'description'         => $request->description,
            'image_path'          => $imagePath,
            'Safety'              => $request->safety ?? 0,
            'Recreation'          => $request->recreation ?? 0,
            'Environment Quality' => $request->environment_quality ?? 0,
            'Facilities'          => $request->facilities ?? 0,
            'Mobility'            => $request->mobility ?? 0,
        ]);

        return redirect()->route('city_functions')->with('success', 'City function created.');
    }

    public function update(Request $request, $id)
    {
        $fn = CityFunction::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions'  => 'nullable|array',
            'conditions.*.target_function_id' => 'required|exists:city_functions,id',
            'conditions.*.type' => 'required|in:required,forbidden',
        ]);

        // Check for contradictory conditions
        $conditionsData = $request->input('conditions', []);
        $this->validateNoContradictions($conditionsData);

        $data = [
            'name'        => $request->name,
            'category'    => $request->category,
            'description' => $request->description,
        ];

        $fn->update($data);

        // Sync conditions
        $existingIds = collect($conditionsData)->pluck('id')->filter()->values();
        $fn->functionConditions()->whereNotIn('id', $existingIds)->delete();

        foreach ($conditionsData as $conditionData) {
            if (isset($conditionData['id']) && $conditionData['id']) {
                $condition = $fn->functionConditions()->find($conditionData['id']);
                if ($condition) {
                    $condition->update([
                        'target_function_id' => $conditionData['target_function_id'],
                        'type' => $conditionData['type'],
                    ]);
                }
            } else {
                $fn->functionConditions()->create([
                    'target_function_id' => $conditionData['target_function_id'],
                    'type' => $conditionData['type'],
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'City function updated.',
                'function' => $fn->load('functionConditions'),
            ]);
        }

        return redirect()->route('city_functions')->with('success', 'City function updated.');
    }

    public function destroy($id)
    {
        CityFunction::findOrFail($id)->delete();

        return redirect()->route('city_functions')->with('success', 'City function deleted.');
    }

    private function validateNoContradictions($conditionsData)
    {
        $grouped = collect($conditionsData)->groupBy('target_function_id');

        foreach ($grouped as $targetId => $conditions) {
            $types = $conditions->pluck('type')->unique();
            if ($types->contains('required') && $types->contains('forbidden')) {
                throw ValidationException::withMessages([
                    'conditions' => ['A contradictory condition exists for target function ' . $targetId . '.'],
                ]);
            }
        }
    }
}
