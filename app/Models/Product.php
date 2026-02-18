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
        'category_id'
    ];

    /**
     * Accessor for the Image URL.
     * This ensures Filament and your API always get the full path.
     */
    public function getImageAttribute($value)
{
    if (!$value) {
        return asset('storage/products/default.jpg');
    }

    // 🔥 Prevents the "Double/Triple URL" crash on the home screen
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
}