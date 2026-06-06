<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventRoute;
use App\Models\AccessRoad;
use App\Models\CityGridCell;
use Illuminate\Support\Facades\DB;

class EventRouteController extends Controller
{
    private const SAFETY_CATEGORY = 'safety';

    /**
     * List all event routes with their cell IDs.
     * SIM.12.2 - Event route index
     */
    public function index()
    {
        $routes = EventRoute::with('cells')->get();

        return response()->json(
            $routes->map(fn ($route) => [
                'id'             => $route->id,
                'access_road_id' => $route->access_road_id,
                'event_cell_id'  => $route->event_cell_id,
                'cell_ids'       => $route->cells->pluck('id')->values()->all(),
            ])
        );
    }

    /**
     * Return all grid cells that currently qualify as event locations.
     * A cell is an event location when its function is linked to at least one city event.
     * SIM.12.2
     */
    public function eventCells()
    {
        $cells = CityGridCell::with('cityFunction')
            ->whereNotNull('function_id')
            ->whereIn('function_id', function ($query) {
                $query->select('city_function_id')->from('city_event_city_function');
            })
            ->get()
            ->map(fn ($cell) => [
                'id'            => $cell->id,
                'row'           => $cell->row_index,
                'column'        => $cell->column_index,
                'function_name' => $cell->cityFunction?->name ?? '',
            ]);

        return response()->json($cells);
    }

    /**
     * Calculate the shortest path from the cells of an access road to an event cell,
     * then persist the resulting route.
     * SIM.12.2 - Create event route
     */
    public function store(Request $request)
    {
        $request->validate([
            'access_road_id' => 'required|integer|exists:access_roads,id',
            'event_cell_id'  => 'required|integer|exists:city_grid_cells,id',
        ]);

        // Ensure the selected cell is actually an event location.
        $isEventCell = DB::table('city_event_city_function')
            ->join('city_grid_cells', 'city_grid_cells.function_id', '=', 'city_event_city_function.city_function_id')
            ->where('city_grid_cells.id', $request->event_cell_id)
            ->exists();

        if (! $isEventCell) {
            return response()->json([
                'message' => 'The selected cell is not an event location. Only cells whose function is linked to a city event can be used as route destinations.',
            ], 422);
        }

        $allCells  = CityGridCell::with('cityFunction')->get();
        $eventCell = $allCells->firstWhere('id', $request->event_cell_id);

        if ($this->hasSafetyFunction($eventCell)) {
            return response()->json([
                'message' => 'The event cell contains a safety category destination. Routes cannot end here.',
            ], 422);
        }

        // Load the road and get its cell IDs.
        $road = AccessRoad::with('cells')->findOrFail($request->access_road_id);
        $roadCellIds = $road->cells->pluck('id')->values()->all();

        if (empty($roadCellIds)) {
            return response()->json([
                'message' => 'The selected access road has no cells.',
            ], 422);
        }

        // Prevent duplicate routes for the same road + event cell pair.
        $duplicate = EventRoute::where('access_road_id', $request->access_road_id)
            ->where('event_cell_id', $request->event_cell_id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'A route from this access road to that event location already exists.',
            ], 422);
        }

        $path = $this->findShortestPathFromRoad($roadCellIds, $eventCell, $allCells);

        if (! $path) {
            return response()->json([
                'message' => 'No valid route exists from the access road to the event location. Safety category destinations may be blocking all paths.',
            ], 422);
        }

        $route = EventRoute::create([
            'user_id'        => auth()->id(),
            'access_road_id' => $request->access_road_id,
            'event_cell_id'  => $request->event_cell_id,
        ]);

        $attachData = [];
        foreach ($path as $order => $cell) {
            $attachData[$cell->id] = ['cell_order' => $order];
        }
        $route->cells()->attach($attachData);

        return response()->json([
            'message' => 'Event route created',
            'route'   => [
                'id'             => $route->id,
                'access_road_id' => $route->access_road_id,
                'event_cell_id'  => $route->event_cell_id,
                'cell_ids'       => collect($path)->pluck('id')->values()->all(),
            ],
        ]);
    }

    /**
     * Remove an event route and all its cell associations.
     * SIM.12.2 - Remove event route
     */
    public function destroy($id)
    {
        $route = EventRoute::findOrFail($id);
        $route->cells()->detach();
        $route->delete();

        return response()->json(['message' => 'Event route removed']);
    }

    /**
     * Re-run BFS for every existing event route using the current road cell positions.
     * Called after a safety function is added or removed, which may have changed both
     * the access road paths (new starting cells) and the route corridors (blocked/unblocked).
     * Returns all routes with their (potentially updated) cell IDs.
     */
    public function recalculateAllEventRoutes(): array
    {
        $allCells = CityGridCell::with('cityFunction')->get();
        $routes   = EventRoute::with(['cells', 'accessRoad.cells'])->get();
        $result   = [];

        foreach ($routes as $route) {
            $currentIds = $route->cells->pluck('id')->values()->all();

            $road = $route->accessRoad;
            if (! $road) {
                $result[] = $this->routePayload($route, $currentIds);
                continue;
            }

            $roadCellIds = $road->cells->pluck('id')->values()->all();
            $eventCell   = $allCells->firstWhere('id', $route->event_cell_id);

            if (! $eventCell || empty($roadCellIds)) {
                $result[] = $this->routePayload($route, $currentIds);
                continue;
            }

            $newPath = $this->findShortestPathFromRoad($roadCellIds, $eventCell, $allCells);

            if (! $newPath) {
                $result[] = $this->routePayload($route, $currentIds);
                continue;
            }

            $newIds = collect($newPath)->pluck('id')->values()->all();

            if ($newIds !== $currentIds) {
                $route->cells()->detach();
                $attachData = [];
                foreach ($newPath as $order => $cell) {
                    $attachData[$cell->id] = ['cell_order' => $order];
                }
                $route->cells()->attach($attachData);
                $result[] = $this->routePayload($route, $newIds);
            } else {
                $result[] = $this->routePayload($route, $currentIds);
            }
        }

        return $result;
    }

    private function routePayload(EventRoute $route, array $cellIds): array
    {
        return [
            'id'             => $route->id,
            'access_road_id' => $route->access_road_id,
            'event_cell_id'  => $route->event_cell_id,
            'cell_ids'       => $cellIds,
        ];
    }

    /**
     * Remove all event routes that reference the given access road.
     * Called from AccessRoadController when a road is deleted.
     */
    public function removeRoutesForRoad(int $accessRoadId): void
    {
        EventRoute::where('access_road_id', $accessRoadId)->each(function (EventRoute $route) {
            $route->cells()->detach();
            $route->delete();
        });
    }

    /**
     * Remove all event routes that end at the given cell.
     * Called from CityGridCellController when a function is removed.
     */
    public function removeRoutesForCell(int $cellId): void
    {
        EventRoute::where('event_cell_id', $cellId)->each(function (EventRoute $route) {
            $route->cells()->detach();
            $route->delete();
        });
    }

    // ── Pathfinding ──────────────────────────────────────────────────────────

    /**
     * Multi-source BFS: find the shortest path from any cell in $roadCellIds to $endCell.
     * Safety-category cells are impassable obstacles (except the end cell).
     */
    private function findShortestPathFromRoad(array $roadCellIds, $endCell, $allCells): ?array
    {
        $cellsById = $allCells->keyBy('id');

        $gridMap = [];
        foreach ($allCells as $cell) {
            $gridMap[$cell->row_index][$cell->column_index] = $cell->id;
        }

        // Seed the BFS queue with every cell belonging to the access road.
        $queue   = [];
        $visited = [];

        foreach ($roadCellIds as $roadCellId) {
            if (! isset($cellsById[$roadCellId])) {
                continue;
            }
            $queue[]              = [$roadCellId, [$roadCellId]];
            $visited[$roadCellId] = true;
        }

        while (! empty($queue)) {
            [$currentId, $path] = array_shift($queue);

            if ($currentId === $endCell->id) {
                return array_map(fn ($id) => $cellsById[$id], $path);
            }

            foreach ($this->getNeighborIds($cellsById[$currentId], $gridMap) as $neighborId) {
                if (isset($visited[$neighborId])) {
                    continue;
                }

                // End cell is always passable; safety cells are not.
                if ($neighborId !== $endCell->id && $this->hasSafetyFunction($cellsById[$neighborId])) {
                    continue;
                }

                $visited[$neighborId] = true;
                $queue[]              = [$neighborId, array_merge($path, [$neighborId])];
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
