<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PrintQuote extends Model
{
    protected $fillable = ['owner_id', 'quote_number', 'name', 'model_name', 'customer', 'status', 'currency', 'source_url', 'external_id', 'image_path', 'source_file_path', 'model_data', 'calculation_data', 'results', 'total_cost_cents', 'final_price_cents', 'valid_until', 'notes'];

    protected function casts(): array
    {
        return ['model_data' => 'array', 'calculation_data' => 'array', 'results' => 'array', 'valid_until' => 'date'];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : ($this->model_data['thumbnailUrl'] ?? null);
    }
}
