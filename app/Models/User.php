<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'category', 'parent_id', 'mother_id', 'father_id',
        'birth_date', 'personal_info', 'profile_photo_path', 'favorite_team', 'theme', 'dashboard_widgets', 'email_notifications', 'push_notifications',
        'calendar_reminder_minutes', 'shopping_archive_days', 'week_starts_on', 'compact_dashboard', 'is_active', 'can_manage_coin_collection',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'dashboard_widgets' => 'array',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'calendar_reminder_minutes' => 'integer',
            'shopping_archive_days' => 'integer',
            'compact_dashboard' => 'boolean',
            'is_active' => 'boolean',
            'can_manage_coin_collection' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function mother()
    {
        return $this->belongsTo(User::class, 'mother_id');
    }

    public function father()
    {
        return $this->belongsTo(User::class, 'father_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isParent(): bool
    {
        return in_array($this->category, ['mamma', 'papa'], true);
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function movieEntries()
    {
        return $this->hasMany(MovieEntry::class);
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? Storage::disk('public')->url($this->profile_photo_path) : null;
    }

    public function avatarInitials(): string
    {
        return Str::of($this->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->join('');
    }
}
