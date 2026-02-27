<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    // Use standard traits for API and authentication
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'can_manage_products',
    'can_manage_categories',
    'can_manage_orders',
    'can_manage_address', // 🟢 Add this here
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relationship: A user can own a shop.
     */
    public function shopkeeper(): HasOne
    {
        return $this->hasOne(Shopkeeper::class);
    }

    /**
     * Relationship: A user can place many orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Security: Determine who can enter the Filament Admin Panel.
     * Prevents normal 'user' roles from accessing the backend.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'shopkeeper']);
    }

    /**
     * Helper to check if the user is a seller.
     */
    public function isShopkeeper(): bool
    {
        return $this->role === 'shopkeeper' || $this->shopkeeper()->exists();
    }

    /**
     * Accessor: Formats the Avatar URL for Flutter.
     * Ensures the app always gets a full URL for the image.
     */
    public function getAvatarAttribute($value)
    {
        if (!$value || $value === 'default.jpg' || $value === 'users/default.jpg') {
            return asset('storage/users/default.jpg');
        }

        return str_starts_with($value, 'http') ? $value : asset('storage/' . $value);
    }

    /**
     * Modern Laravel Hashing.
     * Automatically hashes passwords when saved.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', 
        ];
    }

    /**
     * Relationship: Connects a Shopkeeper to their customers via Orders.
     * Used for the "Total Customers" dashboard widget.
     */
    public function customers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class, 
            Order::class, 
            'shopkeeper_id', // Foreign key on Order table
            'id',            // Foreign key on User table
            'id',            // Local key on User table
            'user_id'        // Local key on Order table
        );
    }
}