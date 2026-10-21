<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Coin extends Model
{
    protected $fillable = [
        'owner_id', 'code', 'country', 'year', 'title', 'denomination_eur', 'type',
        'condition', 'rarity', 'estimated_min_eur', 'estimated_max_eur', 'face_value_eur',
        'probable_duplicate', 'identification_confidence', 'manual_verification', 'image_path', 'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'denomination_eur' => 'decimal:2',
        'estimated_min_eur' => 'decimal:2',
        'estimated_max_eur' => 'decimal:2',
        'face_value_eur' => 'decimal:2',
        'identification_confidence' => 'decimal:3',
        'probable_duplicate' => 'boolean',
        'manual_verification' => 'boolean',
    ];

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
