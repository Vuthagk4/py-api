<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens;
    // app/Models/User.php


    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mobile App Fix: Full Avatar URL
     * Mobile apps need the full URL (http://...) to display images correctly.
     */
    public function getAvatarAttribute($value)
{
    // 1. If empty, give default
    if (!$value || $value === 'default.jpg') {
        return asset('storage/default.jpg');
    }

    // 2. 🔥 THIS STOPS THE LOOP: If it already starts with http, don't touch it!
    if (str_starts_with($value, 'http')) {
        return $value;
    }

    // 3. Otherwise, add storage prefix
    return asset('storage/' . $value);
}

    /**
     * Filament Admin Access
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow login to Admin Panel
        return true; 
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}