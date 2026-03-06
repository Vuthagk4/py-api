<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\FCMService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $messages = [
                'PROCESSING' => "Your order #{$order->id} is being prepared! 🍳",
                'COMPLETED'  => "Order #{$order->id} is ready for delivery! 🛵",
                'CANCELLED'  => "Order #{$order->id} has been cancelled. ❌",
            ];

            if (isset($messages[$order->status]) && $order->user->fcm_token) {
                app(FCMService::class)->sendPushNotification(
                    $order->user->fcm_token,
                    'Order Update',
                    $messages[$order->status],
                    [
                        'order_id' => (string) $order->id,
                        'type' => 'order_status', // 🟢 Tells Flutter to show action buttons
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]
                );
            }
        }
    }
}