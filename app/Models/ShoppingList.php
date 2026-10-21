<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingList extends Model
{
    protected $fillable = ['name', 'list_date', 'owner_id', 'shared_with', 'archived_at'];

    protected function casts(): array
    {
        return ['list_date' => 'date', 'shared_with' => 'array', 'archived_at' => 'datetime'];
    }

    public function items()
    {
        return $this->hasMany(ShoppingItem::class);
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
