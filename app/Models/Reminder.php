<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'title', 'body', 'owner_id', 'due_at', 'recurrence', 'shared_with', 'completed_at', 'push_sent_at',
    ];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'shared_with' => 'array', 'completed_at' => 'datetime', 'push_sent_at' => 'datetime'];
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
