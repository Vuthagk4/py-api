<?php

namespace App\Http\Controllers;

use App\Services\FCMService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $fcmService;
    
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    
    public function sendToUser(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        
        $response = $this->fcmService->sendPushNotification(
            $request->token,
            $request->title,
            $request->body,
            [
                'route' => $request->route ?? 'home',
                'user_id' => $request->user_id ?? null,
            ]
        );
        
        return response()->json(['success' => true, 'response' => $response]);
    }
    
    public function sendToTopic(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        
        $response = $this->fcmService->sendToTopic(
            $request->topic,
            $request->title,
            $request->body,
            [
                'route' => $request->route ?? 'home',
                'type' => $request->type ?? 'general',
                "title"=> $request->title,
                "body"=> $request->body,
            ]
        );
        
        return response()->json(['success' => true, 'response' => $response]);
    }
    // ... existing code ...

public function updateFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    // 🟢 This saves the token to the logged-in user
    $request->user()->update([
        'fcm_token' => $request->fcm_token,
    ]);

    return response()->json([
        'success' => true, 
        'message' => 'Token updated successfully'
    ]);
}
public function index(Request $request)
{
    return $request->user()->notifications;
}
// Mark a single notification as read
public function markAsRead(Request $request, $id) {
    $notification = $request->user()->notifications()->findOrFail($id);
    $notification->markAsRead();
    return response()->json(['success' => true]);
}

// Mark everything as read
public function markAllAsRead(Request $request) {
    $request->user()->unreadNotifications->markAsRead();
    return response()->json(['success' => true]);
}

// Delete a notification
public function destroy(Request $request, $id) {
    $request->user()->notifications()->findOrFail($id)->delete();
    return response()->json(['success' => true]);
}
    
}