<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityGridCell;
use App\Models\CityFunction;
use App\Services\QolScoreService;

class CityGridCellController extends Controller
{
    public function index()
    {
        $cells = CityGridCell::ensureGridExists();

        // return response()->json($cells);
        $cityFunctions = CityFunction::with('functionConditions')->get();
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
        $placedFunction = CityFunction::with('functionConditions')->findOrFail($request->function_id);
        $neighborCells = $this->getAdjacentCells($cell);
        $neighborFunctionIds = $neighborCells->pluck('function_id')->filter()->values()->all();

        // Check if placed function violates its own rules
        $errors = $this->validatePlacementConditions($placedFunction, $neighborFunctionIds);
        if (! empty($errors)) {
            return response()->json(['message' => 'Placement blocked by adjacency conditions', 'errors' => $errors], 422);
        }

        // Check if neighbors would have violated by this placement
        $neighborErrors = $this->validateNeighborConditions($placedFunction, $neighborCells);
        if (! empty($neighborErrors)) {
            return response()->json(['message' => 'Placement blocked by neighbor adjacency conditions', 'errors' => $neighborErrors], 422);
        }

        $cell->update([
            'function_id' => $request->function_id,
        ]);

        return response()->json([
            'message' => 'Function assigned',
            'cell' => $cell
        ]);
    }

    private function getAdjacentCells(CityGridCell $cell)
    {
        $adjacentPositions = [
            ['row' => $cell->row_index - 1, 'column' => $cell->column_index],
            ['row' => $cell->row_index + 1, 'column' => $cell->column_index],
            ['row' => $cell->row_index, 'column' => $cell->column_index - 1],
            ['row' => $cell->row_index, 'column' => $cell->column_index + 1],
        ];

        $query = CityGridCell::query();

        foreach ($adjacentPositions as $position) {
            $query->orWhere(function ($query) use ($position) {
                $query->where('row_index', $position['row'])
                      ->where('column_index', $position['column']);
            });
        }

        return $query->get();
    }

    private function validatePlacementConditions(CityFunction $function, array $neighborFunctionIds)
    {
        $errors = [];

        foreach ($function->functionConditions as $condition) {
            if ($condition->type === 'forbidden' && in_array($condition->target_function_id, $neighborFunctionIds, true)) {
                $errors[] = "Forbidden neighbor function id {$condition->target_function_id} is present next to this cell.";
            }

            if ($condition->type === 'required' && ! in_array($condition->target_function_id, $neighborFunctionIds, true)) {
                $errors[] = "Required neighbor function id {$condition->target_function_id} is missing from adjacent cells.";
            }
        }

        return $errors;
    }

    private function validateNeighborConditions(CityFunction $placedFunction, $neighborCells)
    {
        $errors = [];

        foreach ($neighborCells as $neighborCell) {
            if (!$neighborCell->function_id) {
                continue; // Skip empty cells
            }

            $neighborFunction = CityFunction::with('functionConditions')->find($neighborCell->function_id);
            if (!$neighborFunction) {
                continue;
            }

            // Check if the neighbor has rules that would be violated by placing this function
            foreach ($neighborFunction->functionConditions as $condition) {
                if ($condition->type === 'forbidden' && $condition->target_function_id == $placedFunction->id) {
                    $errors[] = "Neighbor {$neighborFunction->name} forbids {$placedFunction->name}.";
                }
            }
        }

        return $errors;
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
        $result = (new QolScoreService())->calculate();

        return response()->json($result);
    }

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
