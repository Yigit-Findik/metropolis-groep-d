<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityGridCell;

class CityGridController extends Controller
{
    public function index()
    {
        $cells = CityGridCell::ensureGridExists();

        return response()->json($cells);
    }

    public function select($id)
    {
        // Reset all selections
        CityGridCell::query()->update(['is_selected' => false]);

        // Select clicked cell
        $cell = CityGridCell::findOrFail($id);
        $cell->update(['is_selected' => true]);

        return response()->json([
            'message' => 'Cell selected',
            'cell' => $cell
        ]);
    }
    
//assign function
    public function assignFunction(Request $request, $id)
    {
        $request->validate([
            'function_name' => 'required|string|max:255',
        ]);

        $cell = CityGridCell::findOrFail($id);

        if ($cell->function_name !== null) { // checks if the function has a name
            return response()->json(['message' => 'Cell occupied'], 422);
        }
            
        // update the cell's name 

        $cell->update([
            'function_name' => $request->function_name,
        ]);

        // return the view (or actually it's just a list with all cells right now)

        return response()->json([
            'message' => 'Function assigned',
            'cell' => $cell
        ]);
    }
}
