<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityGridCell;
use App\Models\CityFunction;

class CityGridCellController extends Controller
{
    public function index()
    {
        $cells = CityGridCell::ensureGridExists();

        // return response()->json($cells);
        $cityFunctions = CityFunction::all();
        $categories = $cityFunctions->pluck('category')->unique()->filter()->values();

        return view('grid', [
            'gridCells' => $cells,
            'cityFunctions' => $cityFunctions,
            'categories' => $categories,
        ]);

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

    /**
     * Assign a city function to a specific grid cell.
     * 
     * This method handles placing a function (like School, Hospital, etc.)
     * into a grid cell. It validates that the function exists before saving.
     * 
     * SIM.2 - Placing functions in the grid
     */
    public function assignFunction(Request $request, $id)
    {
        $request->validate([
            'function_id' => 'required|integer|exists:city_functions,id',
        ]);

        $cell = CityGridCell::findOrFail($id);

        $cell->update([
            'function_id' => $request->function_id,
        ]);

        return response()->json([
            'message' => 'Function assigned',
            'cell' => $cell
        ]);
    }

    /**
     * Remove a city function from a specific grid cell.
     * 
     * SIM.3 - Subtask 4 & 6: Build Removal API Endpoint + Ensure Other Cells Are Not Affected
     * 
     * This method handles removing a function from a grid cell. It:
     * 1. Validates that the cell exists
     * 2. Checks that the cell actually contains a function (safeguard)
     * 3. Sets the function_id to null (clearing the placement)
     * 4. Only modifies the specified cell (no side effects on other cells)
     * 
     * @param int $id - The ID of the cell to remove the function from
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFunction($id)
    {
        // Find the cell or return 404 if not found
        $cell = CityGridCell::findOrFail($id);

        // Safeguard: Ensure the cell actually has a function before removing
        // This prevents unnecessary operations and provides better error handling
        if (!$cell->function_id) {
            return response()->json([
                'message' => 'Cell does not contain a function',
                'cell' => $cell
            ], 400);
        }

        // Remove the function by setting function_id to null
        // This leaves all other cells completely untouched
        $cell->update([
            'function_id' => null,
        ]);

        return response()->json([
            'message' => 'Function removed successfully',
            'cell' => $cell
        ]);
    }
}
