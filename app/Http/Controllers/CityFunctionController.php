<?php

namespace App\Http\Controllers;

use App\Events\NewFunctionAdded;
use App\Models\CityFunction;
use App\Models\FunctionCondition;
use Illuminate\Http\Request;

class CityFunctionController extends Controller
{
    public function index()
    {
        // Order by category first, then name, so the grid view can render stable grouped sections.
        $cityFunctions = CityFunction::with('functionConditions')->orderBy('category')->orderBy('name')->get();
        $categories = CityFunction::select('category')->distinct()->orderBy('category')->pluck('category');

        return view('city_functions', compact('cityFunctions', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string|max:255',
            'image_alt'           => 'nullable|string|max:255',
            'description'         => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'safety'              => 'nullable|integer|min:-10|max:10',
            'recreation'          => 'nullable|integer|min:-10|max:10',
            'environment_quality' => 'nullable|integer|min:-10|max:10',
            'facilities'          => 'nullable|integer|min:-10|max:10',
            'mobility'            => 'nullable|integer|min:-10|max:10',
        ]);

        $imagePath = '';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/city_functions'), $filename);
            $imagePath = 'images/city_functions/' . $filename;
        }

        $cityFunction = CityFunction::create([
            'name'                => $request->name,
            'category'            => $request->category,
            'description'         => $request->description,
            'image_path'          => $imagePath,
            'image_alt'           => $request->image_alt ?? '',
            'Safety'              => $request->safety ?? 0,
            'Recreation'          => $request->recreation ?? 0,
            'Environment Quality' => $request->environment_quality ?? 0,
            'Facilities'          => $request->facilities ?? 0,
            'Mobility'            => $request->mobility ?? 0,
        ]);

        // EFF.2 Fire the event so effects experts are notified asynchronously.
        // The listener runs on the queue, so this line does not delay the redirect.
        NewFunctionAdded::dispatch($cityFunction);

        return redirect()->route('city_functions')->with('success', 'City function created.');
    }

    public function update(Request $request, $id)
    {
        $fn = CityFunction::findOrFail($id);

        $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string|max:255',
            'image_alt'           => 'nullable|string|max:255',
            'description'         => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'safety'              => 'nullable|integer|min:-10|max:10',
            'recreation'          => 'nullable|integer|min:-10|max:10',
            'environment_quality' => 'nullable|integer|min:-10|max:10',
            'facilities'          => 'nullable|integer|min:-10|max:10',
            'mobility'            => 'nullable|integer|min:-10|max:10',
        ]);

        $data = [
            'name'                => $request->name,
            'category'            => $request->category,
            'description'         => $request->description,
            'image_alt'           => $request->image_alt ?? '',
            'Safety'              => $request->safety ?? 0,
            'Recreation'          => $request->recreation ?? 0,
            'Environment Quality' => $request->environment_quality ?? 0,
            'Facilities'          => $request->facilities ?? 0,
            'Mobility'            => $request->mobility ?? 0,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/city_functions'), $filename);
            $data['image_path'] = 'images/city_functions/' . $filename;
        }

        $fn->update($data);

        // sync_conditions is always sent by the edit form, even when all rules are deleted.
        // Using it as a sentinel avoids the case where an empty conditions array causes
        // $request->has('conditions') to return false and old rules to persist.
        if ($request->boolean('sync_conditions')) {
            $fn->functionConditions()->delete();

            foreach ($request->input('conditions', []) as $conditionData) {
                FunctionCondition::create([
                    'city_function_id'   => $fn->id,
                    'target_function_id' => $conditionData['target_id'],
                    'type'               => $conditionData['type'],
                ]);
            }
        }

        return redirect()->route('city_functions')->with('success', 'City function updated.');
    }

    public function destroy($id)
    {
        CityFunction::findOrFail($id)->delete();

        return redirect()->route('city_functions')->with('success', 'City function deleted.');
    }
}
