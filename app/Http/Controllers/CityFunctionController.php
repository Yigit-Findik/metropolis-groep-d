<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;

class CityFunctionController extends Controller
{
    public function index()
    {
        $cityFunctions = CityFunction::orderBy('category')->orderBy('name')->get();
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

        $data = [
            'name'                => $request->name,
            'category'            => $request->category,
            'description'         => $request->description,
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

        return redirect()->route('city_functions')->with('success', 'City function updated.');
    }

    public function destroy($id)
    {
        CityFunction::findOrFail($id)->delete();

        return redirect()->route('city_functions')->with('success', 'City function deleted.');
    }
}
