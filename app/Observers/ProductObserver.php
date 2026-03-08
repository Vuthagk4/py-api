<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Services\FCMService;
use App\Notifications\NewProductNotification;
use Illuminate\Support\Facades\Notification; // 🟢 Required for Database

class ProductObserver
{
    public function created(Product $product): void
    {
        // 🟢 STEP 1: Save to Database for the Notification List
        // This makes the rows appear in the Flutter NotificationView screen.
        $users = User::where('role', 'user')->get();
        Notification::send($users, new NewProductNotification($product));

        // 🟢 STEP 2: Send Push Notification (FCM)
        $imageUrl = $product->image ? asset('storage/' . $product->image) : null;

        app(FCMService::class)->sendToTopic(
            'all_users',
            '🆕 New Product Alert!',
            "{$product->name} is now available!",
            [
                'product_id' => (string) $product->id,
                'type' => 'product_update'
            ],
            $imageUrl
        );
    }
}