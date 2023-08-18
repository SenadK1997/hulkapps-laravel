<?php

namespace Tests\Unit;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class MovieTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function testSlugCreating()
    {
        $movie = Movie::factory()->create(['title' => 'Test Movie']);
        $this->assertEquals('test-movie', $movie->slug);
    }

    public function testSlugUpdate()
    {
        $movie = Movie::factory()->create(['title' => 'Test Movie']);
        $movie->title = 'Updated Test Movie';
        $movie->updateSlug();
        $this->assertEquals('updated-test-movie', $movie->slug);
    }

    public function testScopeMovies()
    {
        Movie::factory()->create(['title' => 'Movie A']);
        Movie::factory()->create(['title' => 'Movie B']);
        Movie::factory()->create(['title' => 'Another Movie']);

        $movies = Movie::ofType('Movie')->get();
        $this->assertCount(3, $movies);
    }

    public function testFollowers()
    {
        $movie = Movie::factory()->create();
        $user = User::factory()->create();

        $user->followedMovies()->attach($movie);

        $this->assertTrue($movie->followers->contains($user));
    }
}
