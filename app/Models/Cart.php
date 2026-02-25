<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // 🟢 Added
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'status',          // 🟢 Added missing comma here
        'shopkeeper_id',   // 🟢 Now correctly part of the array
    ];

    /**
     * Relationship: A cart has many items (products).
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relationship: A cart belongs to a customer (User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A cart belongs to a Shopkeeper (Owner).
     * This is required for your dashboard stats to work.
     */
    public function shopkeeper(): BelongsTo
    {
        return $this->belongsTo(Shopkeeper::class);
    }
}