<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityGridCell;
use App\Models\CityFunction;
use App\Models\ActionHistory;
use App\Services\QolScoreService;
use App\Models\CityEvent;
use Barryvdh\DomPDF\Facade\Pdf;

class CityGridCellController extends Controller
{
    public function index()
    {
        // Create missing cells on demand so the view always receives a complete grid structure.
        $cells = CityGridCell::ensureGridExists();

        $cityFunctions = CityFunction::orderBy('name')->get();
        $categories = CityFunction::query()->distinct()->orderBy('category')->pluck('category')->filter()->values();

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
        $function = CityFunction::with('functionConditions')->findOrFail($request->function_id);
        
        // Check adjacency conditions
        $error = $this->checkAdjacencyConditions($function, $cell);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $oldFunctionId = $cell->function_id; // Saved so we can record it in ActionHistory before it is replaced

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
     * Check if placing a function violates its adjacency conditions
     */
    private function checkAdjacencyConditions(CityFunction $function, CityGridCell $targetCell)
    {
        $adjacentCells = $this->getAdjacentCells($targetCell);
        $neighborFunctionIds = $adjacentCells->pluck('function_id')->filter()->unique()->values()->all();
        $errors = [];

        // Check the function's own conditions against its future neighbors
        foreach ($function->functionConditions as $condition) {
            if ($condition->type === 'forbidden' && in_array($condition->target_function_id, $neighborFunctionIds)) {
                $targetFn = CityFunction::find($condition->target_function_id);
                $errors[] = "'{$function->name}' cannot be placed next to '{$targetFn->name}' — choose a cell that does not touch '{$targetFn->name}'.";
            }

            if ($condition->type === 'required' && !in_array($condition->target_function_id, $neighborFunctionIds)) {
                $targetFn = CityFunction::find($condition->target_function_id);
                $errors[] = "'{$function->name}' must be placed adjacent to '{$targetFn->name}', but no '{$targetFn->name}' is next to this cell — pick a cell that borders '{$targetFn->name}'.";
            }
        }

        // Bidirectional check: if an already-placed neighbor has a forbidden rule pointing at
        // the function being placed, block the placement from that side too.
        if (!empty($neighborFunctionIds)) {
            $neighborFunctions = CityFunction::with('functionConditions')
                ->whereIn('id', $neighborFunctionIds)
                ->get();

            foreach ($neighborFunctions as $neighbor) {
                foreach ($neighbor->functionConditions as $condition) {
                    if ($condition->type === 'forbidden' && $condition->target_function_id === $function->id) {
                        $errors[] = "'{$neighbor->name}' (already on the grid) forbids being placed next to '{$function->name}' — choose a cell that does not touch '{$neighbor->name}'.";
                    }
                }
            }
        }

        return empty($errors) ? null : implode("\n", $errors);
    }

    /**
     * Get cells adjacent to the target cell (up, down, left, right)
     */
    private function getAdjacentCells(CityGridCell $cell)
    {
        return CityGridCell::where(function ($query) use ($cell) {
            // Up
            $query->where(function ($q) use ($cell) {
                $q->where('row_index', $cell->row_index - 1)
                  ->where('column_index', $cell->column_index);
            })
            // Down
            ->orWhere(function ($q) use ($cell) {
                $q->where('row_index', $cell->row_index + 1)
                  ->where('column_index', $cell->column_index);
            })
            // Left
            ->orWhere(function ($q) use ($cell) {
                $q->where('row_index', $cell->row_index)
                  ->where('column_index', $cell->column_index - 1);
            })
            // Right
            ->orWhere(function ($q) use ($cell) {
                $q->where('row_index', $cell->row_index)
                  ->where('column_index', $cell->column_index + 1);
            });
        })->get();
    }

    public function getQolScore()
    {
        // Calculate the score on demand and return the aggregated result as JSON for the frontend.
        $result = (new QolScoreService())->calculate();

        return response()->json($result);
    }

    /**
     * Get valid and invalid cells for placing a function based on adjacency rules.
     * Returns cell IDs that are valid (green) and invalid (red) for placement.
     */
    public function getValidCells(Request $request)
    {
        $functionId = $request->input('function_id');
        
        if (!$functionId) {
            return response()->json(['valid' => [], 'invalid' => []]);
        }

        $function = CityFunction::with('functionConditions')->find($functionId);
        if (!$function) {
            return response()->json(['valid' => [], 'invalid' => []]);
        }

        // If function has no adjacency conditions, all empty cells are valid
        if ($function->functionConditions->isEmpty()) {
            $allCells = CityGridCell::whereNull('function_id')->pluck('id');
            return response()->json(['valid' => $allCells->all(), 'invalid' => []]);
        }

        $validCells = [];
        $invalidCells = [];
        $allCells = CityGridCell::all();

        foreach ($allCells as $cell) {
            // Skip occupied cells
            if ($cell->function_id) {
                continue;
            }

            $hasError = $this->checkAdjacencyConditions($function, $cell);
            if ($hasError) {
                $invalidCells[] = $cell->id;
            } else {
                $validCells[] = $cell->id;
            }
        }

        return response()->json([
            'valid' => $validCells,
            'invalid' => $invalidCells
        ]);
    }

    // SIM.3 - Clears a function from the given cell and records the removal in ActionHistory.
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

    public function previewPdf()
    {
        return view('pdf.grid-preview', $this->buildReportData());
    }

    public function exportPdf()
    {
        $data = $this->buildReportData();

        $pdf = Pdf::loadView('pdf.grid-report', $data)->setPaper('a4', 'portrait');

        return $pdf->download('city-grid-report-' . now()->format('d-m-Y') . '.pdf');
    }

    private function buildReportData(): array
    {
        $cells         = CityGridCell::ensureGridExists();
        $cityFunctions = CityFunction::orderBy('name')->get();
        $qol           = (new QolScoreService())->calculate();

        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile  = basename($manifest['resources/css/app.css']['file'] ?? 'app.css');

        return [
            'gridCells'     => $cells,
            'cityFunctions' => $cityFunctions,
            'qol'           => $qol,
            'events'        => CityEvent::orderBy('event_type')->orderBy('name')->get(),
            'placedCount'   => $cells->filter(fn ($c) => $c->function_id)->count(),
            'totalCells'    => $cells->count(),
            'author'        => auth()->user()->name,
            'exportedAt'    => now()->format('d F Y, H:i'),
            'cssFile'       => $cssFile,
        ];
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
