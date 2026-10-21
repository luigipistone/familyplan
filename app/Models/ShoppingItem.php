<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingItem extends Model
{
    protected $fillable = ['shopping_list_id', 'name', 'quantity', 'is_checked', 'checked_at'];

    protected function casts(): array
    {
        return ['is_checked' => 'boolean', 'checked_at' => 'datetime'];
    }

    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class);
    }
}
