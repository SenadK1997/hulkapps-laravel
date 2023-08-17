<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'release_year', 'genre'];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new MovieFilterScope);
    // }
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('title', 'like', '%' . $type . '%')
              ->orWhere('release_year', $type)
              ->orWhere('slug', 'like', '%' . $type. '%')
              ->orWhere('genre', 'like', '%' . $type . '%');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movie) {
            $baseSlug = Str::slug($movie->title);
            $slug = $baseSlug;
            $suffix = 1;

            while (static::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }

            $movie->slug = $slug;
        });
    }

}
