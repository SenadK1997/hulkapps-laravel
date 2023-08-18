<?php

namespace App\Http\Controllers;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;


class MovieController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->$user = auth()->user();
    }
    private function userIsAuthenticated()
    {
        return auth()->check();
    }
    protected function validationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'release_year' => 'required|integer',
            'genre' => 'required|string',
        ];
    }
    public function createMovie(Request $request)
    {
        if (!$this->userIsAuthenticated()) {
            return $this->respondWithError('Unauthorized', 401);
        }
    
        $validatedData = $request->validate($this->validationRules());
        $movie = Movie::create($validatedData);
        return response()->json($movie, 201);
    }

    public function updateMovie(Request $request, $id)
    {
        if (!$this->userIsAuthenticated()) {
            return $this->respondWithError('Unauthorized', 401);
        }
        $validatedData = $this->validate($request, $this->validationRules());
        $movie = Movie::findOrFail($id);
        $movie->update($validatedData);
        $movie->updateSlug();
        return response()->json($movie, 200);
    }

    public function deleteMovie($id)
    {
        if (!$this->userIsAuthenticated()) {
            return $this->respondWithError('Unauthorized', 401);
        }
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
    public function cacheFavorite($movieId)
    {
        $user = auth()->user();
        if (!$this->userIsAuthenticated()) {
            return $this->respondWithError('Unauthorized', 401);
        }
        $movie = Movie::findOrFail($movieId);
        $cacheKey = 'user_' . $user->id . '_favorite_' . $movie->id;
        Cache::forever($cacheKey, $movie);
        return response()->json([
            'message' => 'Movie cached as favorite',
            'favorite_movie' => $movie
        ], 200);
    }

    public function searchMovie(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $movies = Movie::ofType($search)->paginate($perPage);
        if ($movies->isEmpty()) {
            return response()->json('Movie not found', 404);
        }
        return response()->json($movies, 200);
    }

    public function getAllMovies(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $movies = Movie::paginate($perPage);
        return response()->json($movies, 200);
    }
    public function toggleFollowMovie($movieId)
    {
        $user = auth()->user();
        if (!$this->userIsAuthenticated()) {
            return $this->respondWithError('Unauthorized', 401);
        }
        $isFollowed = $user->toggleFollowMovie($movieId);
        $message = $isFollowed['attached'] ? 'Movie followed' : 'Movie unfollowed';
        return response()->json(['message' => $message]);
    }
    private function respondWithError($message, $statusCode)
    {
        return response()->json(['error' => $message], $statusCode);
    }
}
