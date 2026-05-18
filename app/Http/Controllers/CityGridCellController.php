<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityGridCell;
use App\Models\CityFunction;
use App\Models\ActionHistory;
use App\Services\QolScoreService;

class CityGridCellController extends Controller
{
    public function index()
    {
        // Create missing cells on demand so the view always receives a complete grid structure.
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
        // Update every row to false first, then flip only the clicked cell to true to keep selection exclusive.
        CityGridCell::query()->update(['is_selected' => false]);

        // Persist the newly selected cell so subsequent requests can restore the active state.
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
        $oldFunctionId = $cell->function_id; // Saving an old city_function_id before it will be replased with a new one

        $cell->update([
            'function_id' => $request->function_id,
        ]);

        // Store the placement so the latest grid action can be undone later.
        ActionHistory::create([
            'user_id' => auth()->id(),
            'action' => 'assign',
            'cell_id' => $id,
            'old_city_function_id' => $oldFunctionId,
            'new_city_function_id' => $request->function_id,
            'details' => [
                'old' => $oldFunctionId,
                'new' => $request->function_id,
            ],
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
    public function getQolScore()
    {
        // Calculate the score on demand and return the aggregated result as JSON for the frontend.
        $result = (new QolScoreService())->calculate();

        return response()->json($result);
    }

    public function removeFunction($id)
    {
        // Load the target cell once so we can validate and update the same record.
        $cell = CityGridCell::findOrFail($id);

        // Skip the write when the cell is already empty; that keeps the API response explicit.
        if (! $cell->function_id) {
            return response()->json([
                'message' => 'Cell does not contain a function',
                'cell' => $cell
            ], 400);
        }

        $oldFunctionId = $cell->function_id;

        // Remove the function by setting function_id to null
        // This leaves all other cells completely untouched
        $cell->update([
            'function_id' => null,
        ]);

        ActionHistory::create([
            'user_id' => auth()->id(),
            'action' => 'remove',
            'cell_id' => $id,
            'old_city_function_id' => $oldFunctionId,
            'new_city_function_id' => null,
            'details' => [
                'old' => $oldFunctionId,
                'new' => null,
            ],
        ]);

        return response()->json([
            'message' => 'Function removed successfully',
            'cell' => $cell
        ]);
    }

    public function undo(){

        $lastAction = ActionHistory::where('user_id', auth()->id())->latest()->first();

        // Checkss that there wasn't any last action that was done. It gives error 400 if the last action is NULL
        if ( !$lastAction ) {
            return response()->json(['message' => 'No action to undo'], 400);
        }

        $cell = CityGridCell::findOrFail($lastAction->cell_id);
        
        // undo the latest function
        $cell->update(['function_id' => $lastAction->old_city_function_id]);

        // Record the undo so the action chain remains reversible.
        ActionHistory::create([
            'user_id' => auth()->id(),
            'action' => 'undo',
            'cell_id' => $lastAction->cell_id,
            'old_city_function_id' => $lastAction->new_city_function_id,
            'new_city_function_id' => $lastAction->old_city_function_id,
            'details' => [
                'reverted_action_id' => $lastAction->id,
                'old' => $lastAction->new_city_function_id,
                'new' => $lastAction->old_city_function_id,
            ],
        ]);

        // Load the old function so frontend knows what to display
        $cell->load('cityFunction');

        return response()->json([
            'message' => 'Action undone',
            'cell' => $cell

        ]);
    }
}
