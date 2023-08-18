<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

class MovieControllerTest extends TestCase
{
    use WithFaker, DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function testCreateMovie()
    {
        $user = User::factory()->create();

        $data = [
            'title' => $this->faker->sentence,
            'release_year' => $this->faker->year,
            'genre' => $this->faker->word,
        ];

        $response = $this->actingAs($user)
                        ->postJson('/api/auth/movies', $data);

        $response->assertStatus(201)
                ->assertJson(['title' => $data['title']]);
    }
    public function testUpdateMovie()
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        $newData = [
            'title' => $this->faker->sentence,
            'release_year' => $this->faker->year,
            'genre' => $this->faker->word,
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/auth/movies/{$movie->id}", $newData);

        $response->assertStatus(200)
                 ->assertJson(['title' => $newData['title']]);
    }

    public function testDeleteMovie()
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)
                         ->deleteJson("/api/auth/movies/{$movie->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Movie deleted successfully']);
        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
    }

    public function testCacheFavorite()
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();
        $response = $this->actingAs($user)->postJson("/api/auth/cache-favorite/{$movie->id}");
        $response->assertStatus(200)->assertJson(['message' => 'Movie cached as favorite']);
        $cacheKey = 'user_' . $user->id . '_favorite_' . $movie->id;
        $this->assertTrue(Cache::has($cacheKey));
    }
    public function testSearchMovie()
    {
        Movie::factory()->count(5)->create();

        $response = $this->getJson('/api/auth/movies/search?search=a');

        $response->assertStatus(200)->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => ['id', 'title', 'release_year', 'genre', 'slug', 'created_at', 'updated_at']
            ],
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links' => [
                '*' => ['url', 'label', 'active']
            ],
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);
    }
    public function testGetAllMovies()
    {
        Movie::factory()->count(10)->create();

        $response = $this->getJson('/api/auth/movies/all');

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'release_year', 'genre', 'slug', 'created_at', 'updated_at']
            ],
            'links' => [
                '*' => ['url', 'label', 'active']
            ],
        ]); 
    }
    public function testToggleFollowMovie()
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/auth/movies/toggle-follow/{$movie->id}");

        $response->assertStatus(200)->assertJson(['message' => 'Movie followed']);
    }

    
}
