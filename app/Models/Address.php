<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name', // Matches Flutter nameController
        'phone',     // Matches Flutter phoneController
        'street',    // Matches Flutter streetController
        'latitude',  // From pickedLocation
        'longitude', // From pickedLocation
        // Keep these if you still need them for other parts of the app
        'city',
        'country',
        'postal_code',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
