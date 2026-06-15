<?php

namespace App\Http\Controllers;

use App\Models\SimulationComment;
use Illuminate\Http\Request;

class SimulationCommentController extends Controller
{
    // REV.2.1 - Return all comments newest-first
    public function index()
    {
        $comments = SimulationComment::with('user')
            ->latest()
            ->get()
            ->map(fn ($comment) => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'author'     => $comment->user->name,
                'is_mine'    => $comment->user_id === auth()->id(),
                'created_at' => $comment->created_at->format('d M Y, H:i'),
            ]);

        return response()->json($comments);
    }

    // REV.2.1 - Add a new comment to the simulation
    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = SimulationComment::create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        $comment->load('user');

        return response()->json([
            'id'         => $comment->id,
            'body'       => $comment->body,
            'author'     => $comment->user->name,
            'is_mine'    => true,
            'created_at' => $comment->created_at->format('d M Y, H:i'),
        ], 201);
    }

    // REV.2.1 - Delete a comment — only the author may do this
    public function destroy($id)
    {
        $comment = SimulationComment::findOrFail($id);

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'You can only delete your own comments.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted.']);
    }
}
