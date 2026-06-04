<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessRoad;
use App\Models\CityGridCell;

class AccessRoadController extends Controller
{
    // Functions whose category blocks access roads from passing through them.
    private const SAFETY_CATEGORY = 'safety';

    public function index()
    {
        $roads = AccessRoad::with('cells')->get();

        return response()->json(
            $roads->map(fn ($road) => [
                'id'       => $road->id,
                'cell_ids' => $road->cells->pluck('id')->values()->all(),
            ])
        );
    }

    /**
     * Calculate the shortest path between two cells and store the resulting road.
     * SIM.12 - Place main access road
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_cell_id' => 'required|integer|exists:city_grid_cells,id',
            'end_cell_id'   => 'required|integer|exists:city_grid_cells,id|different:start_cell_id',
        ]);

        $allCells  = CityGridCell::with('cityFunction')->get();
        $startCell = $allCells->firstWhere('id', $request->start_cell_id);
        $endCell   = $allCells->firstWhere('id', $request->end_cell_id);

        if ($this->hasSafetyFunction($startCell)) {
            return response()->json([
                'message' => 'The start cell contains a safety category destination. Access roads cannot start here.',
            ], 422);
        }

        if ($this->hasSafetyFunction($endCell)) {
            return response()->json([
                'message' => 'The end cell contains a safety category destination. Access roads cannot end here.',
            ], 422);
        }

        $path = $this->findShortestPath($startCell, $endCell, $allCells);

        if (! $path) {
            return response()->json([
                'message' => 'No valid route exists between the selected cells. Safety category destinations may be blocking all paths.',
            ], 422);
        }

        $road = AccessRoad::create([
            'user_id'       => auth()->id(),
            'start_cell_id' => $request->start_cell_id,
            'end_cell_id'   => $request->end_cell_id,
        ]);

        $attachData = [];
        foreach ($path as $order => $cell) {
            $attachData[$cell->id] = ['cell_order' => $order];
        }
        $road->cells()->attach($attachData);

        return response()->json([
            'message' => 'Access road placed',
            'road'    => [
                'id'       => $road->id,
                'cell_ids' => collect($path)->pluck('id')->values()->all(),
            ],
        ]);
    }

    /**
     * Remove an access road and all its cell associations.
     * SIM.12 - Remove main access road
     */
    public function destroy($id)
    {
        $road = AccessRoad::findOrFail($id);
        $road->cells()->detach();
        $road->delete();

        return response()->json(['message' => 'Access road removed']);
    }

    /**
     * BFS to find the shortest path on the grid between start and end cell.
     * Cells with safety category functions are treated as impassable obstacles.
     */
    private function findShortestPath($start, $end, $allCells): ?array
    {
        $cellsById = $allCells->keyBy('id');

        // Build a row/column lookup so we can find orthogonal neighbors in O(1)
        $gridMap = [];
        foreach ($allCells as $cell) {
            $gridMap[$cell->row_index][$cell->column_index] = $cell->id;
        }

        $queue   = [[$start->id, [$start->id]]];
        $visited = [$start->id => true];

        while (! empty($queue)) {
            [$currentId, $path] = array_shift($queue);

            if ($currentId === $end->id) {
                return array_map(fn ($id) => $cellsById[$id], $path);
            }

            foreach ($this->getNeighborIds($cellsById[$currentId], $gridMap) as $neighborId) {
                if (isset($visited[$neighborId])) {
                    continue;
                }

                // End cell is always reachable (it was already validated above)
                if ($neighborId !== $end->id && $this->hasSafetyFunction($cellsById[$neighborId])) {
                    continue;
                }

                $visited[$neighborId] = true;
                $queue[] = [$neighborId, array_merge($path, [$neighborId])];
            }
        }

        return null;
    }

    private function getNeighborIds($cell, array $gridMap): array
    {
        $neighbors  = [];
        $directions = [[-1, 0], [1, 0], [0, -1], [0, 1]];

        foreach ($directions as [$dr, $dc]) {
            $r = $cell->row_index + $dr;
            $c = $cell->column_index + $dc;
            if (isset($gridMap[$r][$c])) {
                $neighbors[] = $gridMap[$r][$c];
            }
        }

        return $neighbors;
    }

    private function hasSafetyFunction($cell): bool
    {
        return $cell->cityFunction !== null
            && strtolower(trim($cell->cityFunction->category ?? '')) === self::SAFETY_CATEGORY;
    }
}