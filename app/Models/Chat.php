<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $fillable = [
        'user_id', 
        'shopkeeper_id', 
        'message', 
        'image_url', 
        'sender_type', 
        'is_read'
    ];

    // Relationship to the Customer
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to the Shopkeeper
    public function shopkeeper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shopkeeper_id');
    }
}
