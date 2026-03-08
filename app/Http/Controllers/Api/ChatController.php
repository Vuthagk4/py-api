<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Product;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // 🟢 Send Message from Flutter to Shopkeeper
    public function sendMessage(Request $request)
    {
        $request->validate([
            'shopkeeper_id' => 'required|exists:users,id',
            'message' => 'required_without:image',
            'image' => 'nullable|image|max:2048',
        ]);

        $chat = new Chat();
        $chat->user_id = Auth::id();
        $chat->shopkeeper_id = $request->shopkeeper_id;
        $chat->sender_type = 'user'; // 🟢 Identifies this as a Customer message

        // Handle Image Upload to CentOS Storage
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chats', 'public');
            $chat->image_url = $path;
        }

        $chat->message = $request->message;
        $chat->save();

        // 🟢 Notify Shopkeeper via FCM
        $shopkeeper = User::find($request->shopkeeper_id);
        if ($shopkeeper && $shopkeeper->fcm_token) {
            app(FCMService::class)->sendPushNotification(
                $shopkeeper->fcm_token,
                'New Message from ' . Auth::user()->name,
                $request->message ?? 'Sent an image 📷',
                ['type' => 'chat', 'user_id' => (string)Auth::id()]
            );
        }

        return response()->json([
            'status' => 'success',
            'data' => $chat
        ]);
        $chat = Chat::create([
        'user_id' => Auth::id(),
        'shopkeeper_id' => $request->shopkeeper_id,
        'message' => $request->message,
        'sender_type' => 'user', // 🟢 MUST match Flutter's 'user' check
    ]);
    return response()->json($chat);
    }

    // 🟢 Fetch Chat History for the Flutter App
    public function getMessages($shopkeeperId)
    {
        $messages = Chat::where('user_id', Auth::id())
            ->where('shopkeeper_id', $shopkeeperId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
    public function index()
{
    // Eager load the shopkeeper to get their name or FCM token if needed
    return Product::with('shopkeeper')->get(); 
}
// 🟢 Update FCM Token for the authenticated user
public function updateFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    $user = Auth::user();
    $user->update(['fcm_token' => $request->fcm_token]);

    return response()->json([
        'status' => 'success',
        'message' => 'FCM Token updated successfully',
    ]);
}
}