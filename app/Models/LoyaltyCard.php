<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'brand',
        'card_number',
        'barcode_value',
        'barcode_format',
        'associated_user_id',
        'favorite_user_ids',
        'photo_path',
        'theme_color',
        'notes',
    ];

    protected $casts = [
        'favorite_user_ids' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function associatedUser()
    {
        return $this->belongsTo(User::class, 'associated_user_id');
    }
}
