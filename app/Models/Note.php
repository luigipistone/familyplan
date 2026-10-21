<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['title', 'body', 'owner_id', 'shared_with', 'archived_at'];

    protected function casts(): array
    {
        return ['shared_with' => 'array', 'archived_at' => 'datetime'];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sharedUsers()
    {
        return $this->morphToMany(User::class, 'shareable', 'shares')->withTimestamps();
    }
}
