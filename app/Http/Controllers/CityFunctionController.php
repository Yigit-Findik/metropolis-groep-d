<?php

namespace App\Http\Controllers;

use App\Events\NewFunctionAdded;
use App\Models\CityFunction;
use Illuminate\Http\Request;

class CityFunctionController extends Controller
{
    public function index()
    {
        // Order by category first, then name, so the grid view can render stable grouped sections.
        $cityFunctions = CityFunction::orderBy('category')->orderBy('name')->get();
        $categories = CityFunction::select('category')->distinct()->orderBy('category')->pluck('category');

        return view('city_functions', compact('cityFunctions', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'safety' => 'nullable|integer|min:0',
            'recreation' => 'nullable|integer|min:0',
            'environment_quality' => 'nullable|integer|min:0',
            'facilities' => 'nullable|integer|min:0',
            'mobility' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Please enter a name for this city function.',
            'category.required' => 'Please select a category for this city function.',
            'image.image' => 'The uploaded file must be an image. Accepted formats: jpeg, png, gif, webp.',
            'image.max' => 'The image is too large. Please upload an image smaller than 4 MB.',
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
            'description'         => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'safety'              => 'nullable|integer|min:0',
            'recreation'          => 'nullable|integer|min:0',
            'environment_quality' => 'nullable|integer|min:0',
            'facilities'          => 'nullable|integer|min:0',
            'mobility'            => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Please enter a name for this city function.',
            'category.required' => 'Please select a category for this city function.',
            'image.image' => 'The uploaded file must be an image. Accepted formats: jpeg, png, gif, webp.',
            'image.max' => 'The image is too large. Please upload an image smaller than 4 MB.',
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
