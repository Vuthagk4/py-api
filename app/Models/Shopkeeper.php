<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shopkeeper extends Model
{
    protected $fillable = [
    'user_id',
    'shop_name',
    'image',
    'telegram_username',
    'phone_number',
    'is_verified',
];

    /**
     * This is what the Filament Select component is looking for!
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship for products count in table
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}