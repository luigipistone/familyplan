<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'expiration_time',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expiration_time' => 'datetime',
        ];
    }
}
