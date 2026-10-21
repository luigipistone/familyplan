<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieEntry extends Model
{
    protected $fillable = ['user_id', 'tmdb_id', 'media_type', 'status', 'is_favorite', 'is_shared', 'rating', 'metadata'];

    protected function casts(): array
    {
        return ['is_favorite' => 'boolean', 'is_shared' => 'boolean', 'rating' => 'integer', 'metadata' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
