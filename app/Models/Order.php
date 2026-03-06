<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',    // 🟢 Linked to shipping_addresses
        'latitude',      // 🟢 New: GPS Latitude
        'longitude',     // 🟢 New: GPS Longitude
        'total_amount',
        'status',
        'shopkeeper_id',
        'image_qrcode'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🟢 Relationship to the Shipping Address
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shopkeeper(): BelongsTo
    {
        return $this->belongsTo(Shopkeeper::class);
    }
}