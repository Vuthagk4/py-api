<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'is_featured',
        'category_id',
        'shopkeeper_id', // 🟢 Fixed: Added missing comma
        'quantity',      // 🟢 Changed from 'stock' to 'quantity' to match your DB
    ];

    /**
     * Accessor for the Image URL.
     * Ensures consistent paths for both Filament and the API.
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return asset('storage/products/default.jpg');
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    /**
     * Relationship to Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relationship to Shopkeeper owner.
     */
    public function shopkeeper(): BelongsTo
    {
        return $this->belongsTo(Shopkeeper::class);
    }
    
}