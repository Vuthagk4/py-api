<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\FCMService;

class ProductObserver
{
    public function created(Product $product): void
    {
        // 🟢 Adding the product image URL to the notification
        $imageUrl = $product->image ? asset('storage/' . $product->image) : null;

        app(FCMService::class)->sendToTopic(
            'all_users',
            '🆕 New Product Alert!',
            "{$product->name} is now available!",
            ['product_id' => (string) $product->id],
            $imageUrl
        );
    }
}