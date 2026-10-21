<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordEntry extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'website',
        'username',
        'password',
        'notes',
        'shared_with',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'notes' => 'encrypted',
            'shared_with' => 'array',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
