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
}