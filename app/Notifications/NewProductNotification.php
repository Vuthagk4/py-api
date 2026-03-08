<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewProductNotification extends Notification
{
    use Queueable;

    protected $product;

    public function __construct($product)
    {
        // 🟢 Pass the product data to the notification
        $this->product = $product;
    }

    public function via($notifiable)
    {
        // 🟢 'database' saves it so the Flutter list can see it
        return ['database'];
    }

    public function toArray($notifiable)
    {
        // 🟢 This matches the 'msgData' keys in your Flutter NotificationView
        return [
            'title' => '🆕 New Product Added!',
            'body' => "{$this->product->name} is now available for \${$this->product->price}!",
            'image' => $this->product->image ? asset('storage/' . $this->product->image) : null,
            'product_id' => $this->product->id,
            'type' => 'product_update',
        ];
    }
}