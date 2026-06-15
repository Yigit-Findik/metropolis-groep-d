<?php

namespace App\Http\Controllers;

use App\Models\GridCellSuggestion;
use App\Models\CityGridCell;
use Illuminate\Http\Request;

class GridCellSuggestionController extends Controller
{
    // REV.2.2 - Return all suggestions with cell location and author
    public function index()
    {
        $suggestions = GridCellSuggestion::with(['cell', 'user'])
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id'          => $s->id,
                'cell_id'     => $s->cell_id,
                'row'         => $s->cell->row_index,
                'column'      => $s->cell->column_index,
                'description' => $s->description,
                'status'      => $s->status,
                'author'      => $s->user->name,
                'is_mine'     => $s->user_id === auth()->id(),
                'created_at'  => $s->created_at->format('d M Y, H:i'),
            ]);

        return response()->json($suggestions);
    }

    // REV.2.2 - Policy maker submits an improvement suggestion for a grid cell
    public function store(Request $request)
    {
        $request->validate([
            'cell_id'     => 'required|integer|exists:city_grid_cells,id',
            'description' => 'required|string|max:1000',
        ]);

        $cell = CityGridCell::findOrFail($request->cell_id);

        $suggestion = GridCellSuggestion::create([
            'cell_id'     => $request->cell_id,
            'user_id'     => auth()->id(),
            'description' => $request->description,
            'status'      => 'pending',
        ]);

        $suggestion->load(['cell', 'user']);

        return response()->json([
            'id'          => $suggestion->id,
            'cell_id'     => $suggestion->cell_id,
            'row'         => $cell->row_index,
            'column'      => $cell->column_index,
            'description' => $suggestion->description,
            'status'      => $suggestion->status,
            'author'      => $suggestion->user->name,
            'is_mine'     => true,
            'created_at'  => $suggestion->created_at->format('d M Y, H:i'),
        ], 201);
    }

    // REV.2.2 - City planner marks a suggestion as accepted or rejected
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $suggestion = GridCellSuggestion::findOrFail($id);
        $suggestion->update(['status' => $request->status]);

        return response()->json([
            'id'     => $suggestion->id,
            'status' => $suggestion->status,
        ]);
    }

    // REV.2.2 - Delete own suggestion
    public function destroy($id)
    {
        $suggestion = GridCellSuggestion::findOrFail($id);

        if ($suggestion->user_id !== auth()->id()) {
            return response()->json(['message' => 'You can only delete your own suggestions.'], 403);
        }

        $suggestion->delete();

        return response()->json(['message' => 'Suggestion deleted.']);
    }
}
