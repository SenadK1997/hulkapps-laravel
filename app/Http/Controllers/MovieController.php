<?php

namespace App\Http\Controllers;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MovieController extends Controller
{
    public function createMovie(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'release_year' => 'required|integer',
            'genre' => 'required|string',
        ]);

        $movie = Movie::create($validatedData);

        return response()->json($movie, 201);
    }

    public function updateMovie(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'release_year' => 'required|integer',
            'genre' => 'required|string',
        ]);

        $movie = Movie::findOrFail($id);

        $movie->update($validatedData);

        return response()->json($movie, 200);
    }

    public function deleteMovie($id)
    {
        $movie = Movie::findOrFail($id);

        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
    public function cacheFavorite($movieId)
    {
        $user = auth()->user();
        $movie = Movie::findOrFail($movieId);
        // if (!$movie) {
        //     return response()->json(['message' => 'Movie not found'], 404);
        // }
        $cacheKey = 'user_' . $user->id . '_favorite_' . $movie->id;

        Cache::forever($cacheKey, $movie);
        return response()->json([
            'message' => 'Movie cached as favorite',
            'favorite_movie' => $movie
        ], 200);
    }
}
