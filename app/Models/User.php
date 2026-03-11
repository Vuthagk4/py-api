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
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'fcm_token',             // 🟢 ADDED: Essential for Real-time Chat
        'shop_name',             // 🟢 ADDED: For Shopkeeper identity
        'telegram_username',     // 🟢 ADDED: For Telegram fallback
        'can_manage_products',
        'can_manage_categories',
        'can_manage_orders',
        'can_manage_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        // 'fcm_token',          // 🟢 REMOVED from hidden: Flutter needs this
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
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Filament Access Security.
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
        return $this->role === 'shopkeeper';
    }

    /**
     * Accessor: Formats the Avatar URL for Flutter.
     */
    public function getAvatarAttribute($value)
    {
        if (!$value || $value === 'default.jpg' || $value === 'users/default.jpg') {
            return asset('storage/users/default.jpg');
        }

        return str_starts_with($value, 'http') ? $value : asset('storage/' . $value);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: Connects Shopkeepers to their Customers.
     */
    public function customers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            Order::class,
            'shopkeeper_id',
            'id',
            'id',
            'user_id'
        );
    }
}
