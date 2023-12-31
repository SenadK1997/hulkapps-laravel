<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function($router){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/movies', [MovieController::class, 'createMovie']);
    Route::put('/movies/{id}', [MovieController::class, 'updateMovie']);
    Route::delete('/movies/{id}', [MovieController::class, 'deleteMovie']);

    Route::post('/cache-favorite/{movieId}', [MovieController::class, 'cacheFavorite']);
    Route::get('/movies/search', [MovieController::class, 'searchMovie']);
    Route::get('/movies/all', [MovieController::class, 'getAllMovies']);

    Route::post('/movies/toggle-follow/{movieId}', [MovieController::class, 'toggleFollowMovie']);
});
