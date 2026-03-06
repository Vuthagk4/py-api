<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FCMService
{
    public function sendPushNotification($token, $title, $body, $data = [], $image = null)
    {
        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);
        
        // 🟢 Attach image if provided
        if ($image) {
            $notification = $notification->withImageUrl($image);
        }

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        if (!empty($data)) {
            // 🟢 Firebase data must always be strings
            $message = $message->withData(array_map('strval', $data));
        }

        return $messaging->send($message);
    }

    public function sendToTopic($topic, $title, $body, $data = [], $image = null)
    {
        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);
        
        if ($image) {
            $notification = $notification->withImageUrl($image);
        }

        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification($notification);

        if (!empty($data)) {
            $message = $message->withData(array_map('strval', $data));
        }

        return $messaging->send($message);
    }
}