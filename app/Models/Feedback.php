<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // 🟢 Explicitly define the table name since "feedback" is its own plural
    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
    ];

    // Relationship: Get the product being reviewed
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship: Get the user who wrote the feedback
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}