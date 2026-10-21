<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title', 'description', 'starts_at', 'ends_at', 'created_by',
        'assigned_user_id', 'child_id', 'visibility', 'color',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function child()
    {
        return $this->belongsTo(User::class, 'child_id');
    }
}
