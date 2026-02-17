<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // Automatically convert "products/filename.jpg" to "http://.../storage/products/filename.jpg"
    public function getImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}